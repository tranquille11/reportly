<?php

namespace App\Models;

use Abbasudo\Purity\Traits\Filterable;
use App\Enums\AgentRole;
use App\Observers\AgentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

#[ObservedBy(AgentObserver::class)]
class Agent extends Model
{
    use Filterable, HasFactory;

    protected $guarded = [];

    protected $filterFields = [
        'role',
        'name',
    ];

    public function scopeAgentData($query, string $start, string $end)
    {
        $start = Carbon::parse($start)->startOfDay();
        $end = Carbon::parse($end)->endOfDay();

        return $query->withCount([
            'inboundCalls' => fn ($q) => $q->whereBetween('start_time', [$start, $end]),
            'callsHungUpUnder30Seconds' => fn ($q) => $q->whereBetween('start_time', [$start, $end]),
            'callsOutboundMissed' => fn ($q) => $q->whereBetween('start_time', [$start, $end]),
            'callsWithHighHoldTime' => fn ($q) => $q->whereBetween('start_time', [$start, $end]),
            'callsWithHighTalkTime' => fn ($q) => $q->whereBetween('start_time', [$start, $end]),
            'callsWithoutDisposition' => fn ($q) => $q->whereBetween('start_time', [$start, $end]),
        ]);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'agent_id');
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class, 'agent_id');
    }

    public function statistics(): HasMany
    {
        return $this->hasMany(AggregateStatistic::class, 'agent_id');
    }

    public function callsWithHighHoldTime()
    {
        return $this->hasMany(Call::class, 'agent_id')->inbound()->overHoldTime();
    }

    public function callsWithHighTalkTime()
    {
        return $this->hasMany(Call::class, 'agent_id')
            ->inbound()
            ->where('talk_time', '>=', 900);
    }

    public function avgTalkTime()
    {
        return $this->hasMany(Call::class, 'agent_id')
            ->selectRaw("avg('talk_time') as avg_talk_time");
    }

    public function inboundCalls()
    {
        return $this->hasMany(Call::class, 'agent_id')->inbound()->chaperone();
    }

    public function callsWithoutDisposition()
    {
        return $this->hasMany(Call::class, 'agent_id')
            ->inbound()
            ->where('disposition_id', null);
    }

    public function callsHungUpUnder30Seconds()
    {
        return $this->hasMany(Call::class, 'agent_id')
            ->where('talk_time', '<', 30)
            ->where('agent_disconnected', true);
    }

    public function callsOutboundMissed()
    {
        return $this->hasMany(Call::class, 'agent_id')
            ->where('call_type', 'outbound_missed');
    }

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'role' => AgentRole::class,
        ];
    }

    public function scopeRepresentative($query)
    {
        return $query->where('role', AgentRole::REPRESENTATIVE);
    }

    public function getInitialsAttribute()
    {
        $names = explode(' ', $this->name);

        return collect($names)
            ->map(fn ($name) => str($name)->take(1)->upper())
            ->take(2)
            ->join('');
    }
}
