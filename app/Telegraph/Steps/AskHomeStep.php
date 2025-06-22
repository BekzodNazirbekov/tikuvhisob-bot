<?php

namespace App\Telegraph\Steps;


use Illuminate\Support\Facades\DB;
use DefStudio\Telegraph\DTO\Message;
use App\Telegraph\State\AddUserState;
use App\Telegraph\Managers\StepManager;
use App\Telegraph\Managers\StateManager;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use App\Telegraph\Contracts\StepInterface;
use DefStudio\Telegraph\Models\TelegraphChat;
use DefStudio\Telegraph\Exceptions\StorageException;

class AskHomeStep implements StepInterface
{
    public function ask(TelegraphChat $chat, bool $edit = false, int $messageId = null): void
    {
        $keyboard = Keyboard::make()->row([
            Button::make("Qo'shish")->action('plus'),
            Button::make("Mening hisobim")->action('balance'),
        ])->row([
            Button::make('⚙️ Sozlamalar')->action('menu')->param('action', 'settings'),
        ]);

        if ($chat->user->role == 'admin') {
            $keyboard->row([
                Button::make("Add User")->action('adduser'),
                Button::make("Add model")->action('balance'),
            ]);
        }

        $text = 'Quyidagilardan birini tanlang';

        if ($edit && $messageId) {
            $chat->edit($messageId)->message($text)->keyboard($keyboard)->send();
        } else {
            $chat->html($text)->keyboard($keyboard)->send();
        }
    }

    public function handleMessage(TelegraphChat $chat, Message $message): void
    {
        $chat->message('Quyidagilaridan birini tanlang?')->send();
        $this->ask($chat);
    }

    /**
     * @throws StorageException
     */
    public
    function handleCallback(TelegraphChat $chat, string $data, $callbackQuery): void
    {
        $decoded = json_decode($data, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            $chat->message('❌ Callback maʼlumot noto‘g‘ri!')->send();
            return;
        }

        $messageId = $callbackQuery->message()->id() ?? null;

        switch ($decoded['action']) {
            case 'plus':
                {
                    $chat->deleteMessage($messageId)->send();
                    StepManager::next($chat);
                }
                break;
            case 'balance':
                {
                    $chat->deleteMessage($messageId)->send();

                    $userId = $chat->user->id;

                    $start = now()->startOfMonth();
                    $end = now()->endOfMonth();

                    $entries = \App\Models\WorkEntry::query()
                        ->select(
                            'parts.model_id',
                            'parts.id as part_id',
                            'parts.price',
                            DB::raw('SUM(quantity) as total_qty'),
                            DB::raw('SUM(quantity * parts.price) as total_sum')
                        )
                        ->join('parts', 'parts.id', '=', 'work_entries.part_id')
                        ->where('user_id', $userId)
                        ->whereBetween('date', [$start, $end])
                        ->groupBy('parts.model_id', 'parts.id', 'parts.price')
                        ->orderBy('parts.model_id')
                        ->get();

                    if ($entries->isEmpty()) {
                        $chat->message("🗓 Hozirgi oyda hech qanday ish yozuvi topilmadi.")->send();
                        return;
                    }

                    $grouped = $entries->groupBy('model_id');
                    $text = "🧾 <b>Hozirgi oy bo‘yicha hisobotingiz:</b>\n\n";

                    foreach ($grouped as $modelId => $group) {
                        $text .= "🧵 <b>Model ID: {$modelId}</b>\n";

                        foreach ($group as $entry) {
                            $sumFormatted = number_format($entry->total_sum, 0, '.', ' ');
                            $priceFormatted = number_format($entry->price, 0, '.', ' ');
                            $text .= "🔸 Part ID: {$entry->part_id} — {$entry->total_qty} dona × {$priceFormatted} = <b>{$sumFormatted} so'm</b>\n";
                        }

                        $text .= "\n";
                    }

                    // 🔙 Orqaga tugmasi
                    $keyboard = Keyboard::make()->buttons([
                        Button::make("⬅️ Orqaga")->action('back'),
                    ]);

                    $chat->html($text)->keyboard($keyboard)->send();
                }
                break;
            case 'adduser':
                {
                    StateManager::setState($chat, AddUserState::class);
                }
                break;
            case 'cancel':
                {
                    $chat->deleteMessage($messageId)->send();
                }
                break;
            case "back":
                {
                    $this->ask($chat, true, $messageId);
                }
                break;
            default:
                {
                    $chat->message('ssad')->send();
                }
                break;
        }

    }
}
