<?php

namespace App\Services;

use App\Models\Agent;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GorgiasService
{
    private $auth;

    private $headers;

    public function __construct()
    {
        $this->headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Basic '.base64_encode(env('GORGIAS_API_USER').':'.env('GORGIAS_API_KEY')),
        ];

        $this->auth = $this->connectAPI();

    }

    private function connectAPI(): PendingRequest
    {
        return Http::withHeaders($this->headers);
    }

    // public function fetchStatistic(string $statistic, ?Agent $agent = null, array $channels)
    // {

    //     $filters = [
    //         'period' => [
    //             'start_datetime' => $start->toAtomString(),
    //             'end_datetime' => $end->toAtomString()
    //         ],
    //         'channels' => $channels
    //     ];

    //     if (!is_null($agent)) {

    //         $filters['agents'] = [$agent->settings['gorgias_user_id']];

    //         if ($agent->settings['gorgias_user_id'] == '') {
    //             try {
    //                 $agent = $this->syncGorgiasUserId($agent);
    //             } catch (GorgiasUserNotFoundException $e) {
    //                 return;
    //             }
    //         }
    //     }

    //     return json_decode($this->auth->post(
    //         "https://stevemadden.gorgias.com/api/stats/{$statistic}",
    //         [
    //             'filters' => $filters
    //         ]
    //     ));
    // }

    // public function fetchViews(array|bool $cursors): bool|array
    // {
    //     $views = [
    //         "emails" => "https://stevemadden.gorgias.com/api/tickets?cursor=&limit=100&order_by=created_datetime%3Adesc&view_id=1204212",
    //         "chats" => "https://stevemadden.gorgias.com/api/tickets?cursor=&limit=100&order_by=created_datetime%3Adesc&view_id=1204245",
    //         "social" => "https://stevemadden.gorgias.com/api/tickets?cursor=&limit=100&order_by=created_datetime%3Adesc&view_id=1205654",
    //         "helpcenter" => "https://stevemadden.gorgias.com/api/tickets?cursor=&limit=100&order_by=created_datetime%3Adesc&view_id=1208425",
    //     ];

    //     if (is_array($cursors)) {
    //         foreach ($cursors as $key => $cursor) {
    //             if ($cursor !== null) {
    //                 $views[$key] = Str::replace('cursor=', "cursor={$cursor}", $views[$key]);
    //             } else {
    //                 unset($cursors[$key]);
    //             }
    //         }
    //         if ($diffArray = array_diff_key($views, $cursors)) {
    //             foreach ($diffArray as $key => $view) {
    //                 unset($views[$key]);
    //             }
    //         }
    //     }

    //     if (!is_bool($cursors)) {
    //         if (count($cursors) !== count($views)) {
    //             throw new \Exception('Number of views and cursors do not match');
    //         } elseif (empty(array_filter($cursors, fn ($item) => !is_null($item)))) {
    //             return false;
    //         }
    //     }

    //     return Http::pool(function (Pool $pool) use ($views) {
    //         foreach ($views as $name => $view) {
    //             $pool->as($name)->withHeaders($this->headers)->get($view);
    //         }
    //     });
    // }

    public function fetchAgentsOverviewStatistics(Agent $agent, string $start, string $end)
    {

        return Http::pool(function (Pool $pool) use ($agent, $start, $end) {
            $pool->as('closed-emails')->withHeaders($this->headers)->post('https://stevemadden.gorgias.com/api/stats/total-tickets-closed',
                [
                    'filters' => [
                        'period' => [
                            'start_datetime' => $start,
                            'end_datetime' => $end,
                        ],
                        'agents' => [$agent->settings['gorgias_user_id']],
                        'channels' => ['email', 'help-center'],
                    ],
                ]);

            $pool->as('closed-chats')->withHeaders($this->headers)->post('https://stevemadden.gorgias.com/api/stats/total-tickets-closed',
                [
                    'filters' => [
                        'period' => [
                            'start_datetime' => $start,
                            'end_datetime' => $end,
                        ],
                        'agents' => [$agent->settings['gorgias_user_id']],
                        'channels' => ['chat'],
                    ],
                ]);

            $pool->as('onetouch')->withHeaders($this->headers)->post('https://stevemadden.gorgias.com/api/stats/total-one-touch-tickets',
                [
                    'filters' => [
                        'period' => [
                            'start_datetime' => $start,
                            'end_datetime' => $end,
                        ],
                        'agents' => [$agent->settings['gorgias_user_id']],
                        'channels' => ['email', 'help-center'],
                    ],
                ]);

            $pool->as('rating-email')->withHeaders($this->headers)->post('https://stevemadden.gorgias.com/api/stats/satisfaction-surveys',
                [
                    'filters' => [
                        'period' => [
                            'start_datetime' => $start,
                            'end_datetime' => $end,
                        ],
                        'agents' => [$agent->settings['gorgias_user_id']],
                        'channels' => ['email', 'help-center'],
                    ],
                ]);

            $pool->as('rating-chat')->withHeaders($this->headers)->post('https://stevemadden.gorgias.com/api/stats/satisfaction-surveys',
                [
                    'filters' => [
                        'period' => [
                            'start_datetime' => $start,
                            'end_datetime' => $end,
                        ],
                        'agents' => [$agent->settings['gorgias_user_id']],
                        'channels' => ['chat'],
                    ],
                ]);

        });
    }

    public function syncGorgiasUserId(Agent $agent): void
    {
        $currentAgent = $this->fetchUsers()->where('name', $agent->stage_name)->first();

        $agent->update(['settings->gorgias_user_id' => $currentAgent['id'] ?? null]);
    }

    public function createRFCView(string $startDate, string $endDate)
    {

        return $this->auth->post('https://stevemadden.gorgias.com/api/views', [
            'visibility' => 'private',
            'name' => 'api-view',
            'slug' => 'api-view',
            'shared_with_users' => [888710840],
            'filters' => "lte(ticket.created_datetime, '{$endDate}') && gte(ticket.created_datetime, '{$startDate}') && containsAny(ticket.channel, ['email', 'help-center', 'chat', 'facebook', 'facebook-mention', 'facebook-messenger', 'facebook-recommendations', 'instagram-ad-comment', 'instagram-comment', 'instagram-mention', 'instagram-direct-message'])",
        ]);

    }

    public function fetchViewTickets(int $id, ?string $cursor = null)
    {
        $url = Str::replace('cursor=', "cursor={$cursor}", "https://stevemadden.gorgias.com/api/tickets?cursor=&limit=100&order_by=created_datetime%3Adesc&view_id={$id}");

        return $this->auth->get($url);
    }

    public function deleteView(int $id)
    {
        return $this->auth->delete("https://stevemadden.gorgias.com/api/views/{$id}");
    }

    public function fetchUsers(): Collection
    {
        $uri = 'https://stevemadden.gorgias.com/api/users?cursor=&limit=100&order_by=name%3Aasc';

        $obj = json_decode($this->auth->get($uri)->body());

        $users = (array) $obj->data;
        $next_cursor = $obj->meta->next_cursor;

        while ($next_cursor !== null) {
            $next_uri = Str::replace('cursor=', "cursor={$next_cursor}", $uri);
            $response = json_decode($this->auth->get($next_uri)->body());

            $next_cursor = $response->meta->next_cursor;
            $next_users = $response->data;

            $users = array_merge($users, (array) $next_users);
        }

        return collect($users)->map(fn ($item) => (array) $item);
    }
}
