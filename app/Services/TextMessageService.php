<?php

namespace App\Services;

use App\Models\TextMessage;
use Illuminate\Support\Str;

class TextMessageService
{
    private static function sendTextMessage($data, $record) : array
    {
        $message = Str::replace('{name}', $record->name, $data['message']);

        //code to call APIs to send SMS

        return [
            'message' => $message,
            'sent_by' => auth()?->id() ?? null,
            'status' => TextMessage::STATUS['PENDING'],
            'response' => '',
            'sent_to' => $record->id,
            'remarks' => $data['remarks'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public static function sendBulkSms($data, $records): void
    {
        $textMessages = collect([]);

        $records->map(function ($record) use($data, $textMessages) {
            $textMessage = self::sendTextMessage($data, $record );
            $textMessages->push($textMessage);
        });

        TextMessage::insert($textMessages->toArray());
    }
}
