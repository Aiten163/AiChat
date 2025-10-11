<?php

namespace App\Orchid\Screens;

use App\Models\ChatMessage;
use App\Orchid\Layouts\MessagesTable;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class MessagesScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): array
    {
        return [
            'messages' => ChatMessage::with(['chat.user'])
                ->filters()
                ->defaultSort('id', 'desc')
                ->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'История сообщений чатов';
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): array
    {
        return [
            MessagesTable::class,
        ];
    }
}
