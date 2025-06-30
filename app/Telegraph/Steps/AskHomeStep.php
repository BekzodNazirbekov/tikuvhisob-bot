<?php

namespace App\Telegraph\Steps;

use App\Models\Model;
use App\Models\Part;
use App\Models\User;
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
    private const PAGE_EDIT_LIMIT_PART = 'edit-limit-part';

    public function ask(TelegraphChat $chat, bool $edit = false, ?int $messageId = null): void
    {
        $keyboard = $this->buildHomeKeyboard($chat);

        $text = "<b>🏠 Asosiy menyu</b>\n\nQuyidagilardan birini tanlang:";

        $edit && $messageId
            ? $chat->edit($messageId)->message($text)->keyboard($keyboard)->send()
            : $chat->html($text)->keyboard($keyboard)->send();
    }

    public function handleMessage(TelegraphChat $chat, Message $message): void
    {
        if ($chat->storage()->get('page') === self::PAGE_EDIT_LIMIT_PART) {
            $this->handleEditLimitPart($chat, $message->text(), $message);
        } else {
            $this->ask($chat);
        }
    }

    public function handleCallback(TelegraphChat $chat, string $data, $callbackQuery): void
    {
        $decoded = json_decode($data, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            $chat->message("❌ Callback maʼlumot noto‘g‘ri!")->send();
            return;
        }

        $messageId = $callbackQuery->message()->id() ?? null;
        $action = $decoded['action'] ?? '';

        if (str_starts_with($action, 'user_balance-')) {
            $userId = (int) str_replace('user_balance-', '', $action);
            $this->showUserBalance($chat, $userId, $messageId);
            return;
        }

        if (str_starts_with($action, 'model_parts-')) {
            $this->showModelParts($chat, $action, $messageId);
            return;
        }

        if (str_starts_with($action, 'part-home-')) {
            $this->showHomePart($chat, $action, $messageId);
            return;
        }

        if (str_starts_with($action, 'edit-part-count-')) {
            $this->editLimitPart($chat, $action, $messageId);
            return;
        }

        if (str_starts_with($action, 'delete-part-')) {
            $this->deletePart($chat, $action, $messageId);
            return;
        }

        match ($action) {
            'add_part' => $this->startAddPart($chat, $messageId),
            'add_model' => $this->startAddModel($chat, $messageId),
            'model' => $this->listModels($chat, $messageId),
            'all_users' => $this->getAllUsers($chat, $messageId),
            'plus' => $this->startAddWork($chat, $messageId),
            'balance' => $this->showBalance($chat, $messageId),
            'adduser' => StateManager::setState($chat, AddUserState::class),
            'cancel' => $chat->deleteMessage($messageId)->send(),
            'back' => $this->ask($chat, true, $messageId),
            default => $chat->message("❗ Nomaʼlum amal tanlandi.")->send(),
        };
    }

    private function buildHomeKeyboard(TelegraphChat $chat): Keyboard
    {
        $keyboard = Keyboard::make()->row([
            Button::make("➕ Ish qo‘shish")->action('plus'),
            Button::make("💰 Mening hisobim")->action('balance'),
        ]);

        if ($chat->user->role === 'admin') {
            $keyboard->row([
                Button::make("👤 Foydalanuvchi qo‘shish")->action('adduser'),
                Button::make("📦 Modellar")->action('model'),
            ])->row([
                Button::make("Barcha foydalanuvchilar")->action('all_users'),
            ]);
        }

        return $keyboard;
    }

    private function handleEditLimitPart(TelegraphChat $chat, ?string $text,Message $message): void
    {
        if (!is_numeric($text)) {
            $chat->message('raqam yuboring')->send();
            return;
        }

        $partId = $chat->storage()->get('limit-edit-part-id');
        $part = Part::find($partId);

        if (!$part) {
            $chat->message("❌ Qism topilmadi.")->send();
            return;
        }

        $part->update(['count' => (int) $text]);
        $chat->message('Limit o‘zgardi.')->send();

        // Return to the part's edit view
        $this->showHomePart($chat, "part-home-{$partId}", $message->id());
    }

    private function editLimitPart(TelegraphChat $chat, string $action, ?int $messageId): void
    {
        $partId = (int) str_replace('edit-part-count-', '', $action);
        $part = Part::where('id', $partId)->where('status', true)->first();

        if (!$part) {
            $chat->message("❌ Qism topilmadi.")->send();
            return;
        }

        $chat->deleteMessage($messageId)->send();
        $chat->storage()->set('page', self::PAGE_EDIT_LIMIT_PART);
        $chat->storage()->set('limit-edit-part-id', $partId);
        $chat->message("O‘zgartirilish kerak bo‘lgan soni yuboring.")->send();
    }

    private function deletePart(TelegraphChat $chat, string $action, ?int $messageId): void
    {
        $partId = (int) str_replace('delete-part-', '', $action);
        $part = Part::where('id', $partId)->where('status', true)->first();

        if (!$part) {
            $chat->message("❌ Qism topilmadi.")->send();
            return;
        }

        $part->status = 'inactive';
        $part->save();
        $chat->message("✅ Qism o‘chirildi.")->send();

        // Return to the model parts view
        $this->showModelParts($chat, "model_parts-{$part->model_id}", $messageId);
    }

    private function showHomePart(TelegraphChat $chat, string $action, ?int $messageId): void
    {
        $partId = (int) str_replace('part-home-', '', $action);
        $part = Part::where('id', $partId)->where('status', true)->first();

        if (!$part) {
            $chat->message("❌ Qism topilmadi.")->send();
            return;
        }

        $chat->deleteMessage($messageId)->send();

        $keyboard = Keyboard::make()->row([
            Button::make("✏️ Limitni o‘zgartirish")->action("edit-part-count-{$part->id}"),
            Button::make("🗑 O‘chirish")->action("delete-part-{$part->id}"),
        ])->row([
            Button::make("⬅️ Orqaga")->action('model'),
        ]);

        $text = "Part name: {$part->name}\nPart limit: {$part->count}\n";

        $chat->html($text)->keyboard($keyboard)->send();
    }

    private function showModelParts(TelegraphChat $chat, string $action, ?int $messageId): void
    {
        $modelId = (int) str_replace('model_parts-', '', $action);
        $model = Model::find($modelId);

        if (!$model) {
            $chat->message("❌ Model topilmadi.")->send();
            return;
        }

        $chat->deleteMessage($messageId)->send();

        $parts = Part::where('model_id', $modelId)->where('status', true)->get();
        $text = "<b>🧩 Model: {$model->name}</b>\n\n";
        $keyboard = Keyboard::make();

        if ($parts->isEmpty()) {
            $text .= "🔕 Bu modelga hech qanday partiya biriktirilmagan.";
        } else {
            foreach ($parts as $part) {
                $sum = number_format($part->price, 0, '.', ' ');
                $text .= "🔹 <b>{$part->name}</b> — <i>{$sum} so'm</i>\n";
                $keyboard->row([Button::make($part->name)->action("part-home-{$part->id}")]);
            }
        }

        $chat->storage()->set('add-part-model-id', $modelId);
        $keyboard->row([
            Button::make("➕ Qism qo‘shish")->action('add_part'),
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
            $keyboard->row([Button::make("ID: {$model->id} - {$model->name}")->action("model_parts-{$model->id}")]);
        }

        $keyboard->row([
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

        $entries = $this->getWorkEntries($chat->user->id);

        if ($entries->isEmpty()) {
            $chat->message("📭 Hozirgi oyda ish yozuvi topilmadi.")->send();
            return;
        }

        $text = $this->buildBalanceText($entries, "🧾 <b>Oy bo‘yicha ish hisobot:</b>\n\n");
        $keyboard = Keyboard::make()->row([Button::make("⬅️ Orqaga")->action('back')]);

        $chat->html($text)->keyboard($keyboard)->send();
    }

    public function getAllUsers(TelegraphChat $chat, ?int $messageId): void
    {
        $chat->deleteMessage($messageId)->send();
        $users = User::all();

        if ($users->isEmpty()) {
            $chat->message("❗ Hozircha hech qanday foydalanuvchi mavjud emas.")->send();
            return;
        }

        $keyboard = Keyboard::make();

        foreach ($users as $user) {
            $keyboard->row([Button::make("👤 {$user->name}")->action("user_balance-{$user->id}")]);
        }

        $keyboard->row([Button::make("⬅️ Orqaga")->action('back')]);

        $chat->html("<b>👥 Foydalanuvchilardan birini tanlang:</b>")->keyboard($keyboard)->send();
    }

    private function showUserBalance(TelegraphChat $chat, int $userId, ?int $messageId): void
    {
        $chat->deleteMessage($messageId)->send();

        $user = User::find($userId);
        if (!$user) {
            $chat->message("❌ Foydalanuvchi topilmadi.")->send();
            return;
        }

        $entries = $this->getWorkEntries($userId);

        if ($entries->isEmpty()) {
            $chat->message("📭 <b>{$user->name}</b> hozirgi oyda hech qanday ish yozuvi kiritmagan.")->send();
            return;
        }

        $text = $this->buildBalanceText($entries, "🧾 <b>{$user->name} (ID: {$user->id})</b>ning hozirgi oy bo‘yicha hisobot:\n\n");
        $keyboard = Keyboard::make()->row([Button::make("⬅️ Orqaga")->action('all_users')]);

        $chat->html($text)->keyboard($keyboard)->send();
    }

    private function getWorkEntries(int $userId)
    {
        return WorkEntry::query()
            ->select(
                'parts.model_id',
                'parts.id as part_id',
                'parts.name as part_name',
                'models.name as model_name',
                'parts.price',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(quantity * parts.price) as total_sum')
            )
            ->join('parts', 'parts.id', '=', 'work_entries.part_id')
            ->join('models', 'models.id', '=', 'parts.model_id')
            ->where('user_id', $userId)
            ->where('parts.status', true)
            ->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])
            ->groupBy('parts.model_id', 'parts.id', 'parts.name', 'models.name', 'parts.price')
            ->orderBy('models.name')
            ->get();
    }

    private function buildBalanceText($entries, string $header): string
    {
        $text = $header;
        $grouped = $entries->groupBy('model_name');

        foreach ($grouped as $modelName => $group) {
            $text .= "🧵 <b>Model: {$modelName}</b>\n";
            foreach ($group as $entry) {
                $price = number_format($entry->price, 0, '.', ' ');
                $total = number_format($entry->total_sum, 0, '.', ' ');
                $text .= "🔸 Qism: {$entry->part_name} — {$entry->total_qty} dona × {$price} = <b>{$total} so'm</b>\n";
            }
            $text .= "\n";
        }

        return $text;
    }
}
