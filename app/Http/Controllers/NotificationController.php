<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function unread(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->unreadNotifications()
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (DatabaseNotification $notification) => $this->formatNotification($notification));

        return response()->json([
            'notifications' => $notifications,
        ]);
    }

    public function markAsRead(Request $request, string $notification): JsonResponse
    {
        $record = $request->user()
            ->unreadNotifications()
            ->whereKey($notification)
            ->firstOrFail();

        $record->markAsRead();

        return response()->json(['ok' => true]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['ok' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatNotification(DatabaseNotification $notification): array
    {
        /** @var array<string, mixed> $data */
        $data = $notification->data;

        return [
            'id' => $notification->id,
            'title' => (string) ($data['title'] ?? __('Workplace update')),
            'body' => (string) ($data['body'] ?? ''),
            'action_url' => $data['action_url'] ?? null,
            'created_at' => $notification->created_at?->toIso8601String(),
        ];
    }
}
