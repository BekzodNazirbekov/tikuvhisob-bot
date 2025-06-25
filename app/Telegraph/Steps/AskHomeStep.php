<?php

namespace App\Telegraph\Steps;

use App\Models\Model;
use App\Models\Part;
use App\Models\WorkEntry;
use App\Telegraph\State\AddModelState;
use App\Telegraph\State\AddPartState;
use App\Telegraph\State\AddUserState;
use App\Telegraph\Managers\StepManager;
use App\Telegraph\Managers\StateManager;
use DefStudio\Telegraph\DTO\Message;
use DefStudio\Telegraph\Exceptions\StorageException;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use App\Telegraph\Contracts\StepInterface;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Facades\DB;

class AskHomeStep implements StepInterface
{
    public function ask(TelegraphChat $chat, bool $edit = false, int $messageId = null): void
    {
        $keyboard = Keyboard::make()
            ->row([
                Button::make("➕ Ish qo‘shish")->action('plus'),
                Button::make("💰 Mening hisobim")->action('balance'),
            ]);

        if ($chat->user->role === 'admin') {
            $keyboard->row([
                Button::make("👤 Foydalanuvchi qo‘shish")->action('adduser'),
                Button::make("📦 Modellar")->action('model'),
            ]);
        }

        $text = "<b>🏠 Asosiy menyu</b>\n\nQuyidagilardan birini tanlang:";

        $edit && $messageId
            ? $chat->edit($messageId)->message($text)->keyboard($keyboard)->send()
            : $chat->html($text)->keyboard($keyboard)->send();
    }

    public function handleMessage(TelegraphChat $chat, Message $message): void
    {
        $this->ask($chat);
    }

    /**
     * @throws StorageException
     */
    public function handleCallback(TelegraphChat $chat, string $data, $callbackQuery): void
    {
        $decoded = json_decode($data, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            $chat->message("❌ Callback maʼlumot noto‘g‘ri!")->send();
            return;
        }

        $messageId = $callbackQuery->message()->id() ?? null;

        // Model detail view (parts)
        if (str_starts_with($decoded['action'] ?? '', 'model_parts-')) {
            $this->showModelParts($chat, $decoded['action'], $messageId);
            return;
        }

        match ($decoded['action']) {
            'add_part' => $this->startAddPart($chat, $messageId),
            'add_model' => $this->startAddModel($chat, $messageId),
            'model' => $this->listModels($chat, $messageId),
            'plus' => $this->startAddWork($chat, $messageId),
            'balance' => $this->showBalance($chat, $messageId),
            'adduser' => StateManager::setState($chat, AddUserState::class),
            'cancel' => $chat->deleteMessage($messageId)->send(),
            'back' => $this->ask($chat, true, $messageId),
            default => $chat->message("❗ Nomaʼlum amal tanlandi.")->send(),
        };
    }

    private function showModelParts(TelegraphChat $chat, string $action, ?int $messageId): void
    {
        $modelId = (int)str_replace('model_parts-', '', $action);
        $model = Model::find($modelId);

        if (!$model) {
            $chat->message("❌ Model topilmadi.")->send();
            return;
        }

        $chat->deleteMessage($messageId)->send();

        $parts = Part::where('model_id', $modelId)->get();
        $text = "<b>🧩 Model: {$model->name}</b>\n\n";

        if ($parts->isEmpty()) {
            $text .= "🔕 Bu modelga hech qanday partiya biriktirilmagan.";
        } else {
            foreach ($parts as $part) {
                $sum = number_format($part->price, 0, '.', ' ');
                $text .= "🔹 <b>{$part->name}</b> — <i>{$sum} so'm</i>\n";
            }
        }

        $chat->storage()->set('add-part-model-id', $modelId);

        $keyboard = Keyboard::make()->buttons([
            Button::make("➕ Qisim qo‘shish")->action("add_part"),
            Button::make("⬅️ Orqaga")->action('model'),
        ]);

        $chat->html($text)->keyboard($keyboard)->send();
    }

    private function startAddPart(TelegraphChat $chat, ?int $messageId): void
    {
        $chat->deleteMessage($messageId)->send();
        StateManager::setState($chat, AddPartState::class);
    }

    private function startAddModel(TelegraphChat $chat, ?int $messageId): void
    {
        $chat->deleteMessage($messageId)->send();
        StateManager::setState($chat, AddModelState::class);
    }

    private function listModels(TelegraphChat $chat, ?int $messageId): void
    {
        $chat->deleteMessage($messageId)->send();
        $models = Model::all();

        $text = $models->isEmpty()
            ? "❗ Hozircha hech qanday model mavjud emas."
            : "<b>📦 Mavjud modellar:</b>\n\n";

        $keyboard = Keyboard::make();

        foreach ($models as $model) {
            $keyboard->buttons([
                Button::make("ID: {$model->id} - {$model->name}")->action("model_parts-{$model->id}")
            ])->chunk(2);
        }

        $keyboard->buttons([
            Button::make("➕ Model qo‘shish")->action('add_model'),
            Button::make("⬅️ Asosiy menyu")->action('back'),
        ]);

        $chat->html($text)->keyboard($keyboard)->send();
    }

    private function startAddWork(TelegraphChat $chat, ?int $messageId): void
    {
        $chat->deleteMessage($messageId)->send();
        StepManager::next($chat);
    }

    private function showBalance(TelegraphChat $chat, ?int $messageId): void
    {
        $chat->deleteMessage($messageId)->send();

        $entries = WorkEntry::query()
            ->select(
                'parts.model_id',
                'parts.id as part_id',
                'parts.price',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(quantity * parts.price) as total_sum')
            )
            ->join('parts', 'parts.id', '=', 'work_entries.part_id')
            ->where('user_id', $chat->user->id)
            ->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])
            ->groupBy('parts.model_id', 'parts.id', 'parts.price')
            ->orderBy('parts.model_id')
            ->get();

        if ($entries->isEmpty()) {
            $chat->message("📭 Hozirgi oyda ish yozuvi topilmadi.")->send();
            return;
        }

        $grouped = $entries->groupBy('model_id');
        $text = "🧾 <b>Oy bo‘yicha ish hisobot:</b>\n\n";

        foreach ($grouped as $modelId => $group) {
            $text .= "🧵 <b>Model ID: {$modelId}</b>\n";
            foreach ($group as $entry) {
                $price = number_format($entry->price, 0, '.', ' ');
                $total = number_format($entry->total_sum, 0, '.', ' ');
                $text .= "🔸 Part ID: {$entry->part_id} — {$entry->total_qty} dona × {$price} = <b>{$total} so'm</b>\n";
            }
            $text .= "\n";
        }

        $keyboard = Keyboard::make()->buttons([
            Button::make("⬅️ Orqaga")->action('back'),
        ]);

        $chat->html($text)->keyboard($keyboard)->send();
    }
}
