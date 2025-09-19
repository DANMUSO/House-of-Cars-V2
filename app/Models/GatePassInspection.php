<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GatePassInspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'gate_pass_id',
        'submitted_by',
        'spare_wheel_present',
        'wheel_spanner_present',
        'jack_present',
        'life_saver_present',
        'first_aid_kit_present',
        'spare_wheel_absent',
        'wheel_spanner_absent',
        'jack_absent',
        'life_saver_absent',
        'first_aid_kit_absent',
        'comments',
    ];

    protected $casts = [
        'spare_wheel_present' => 'boolean',
        'wheel_spanner_present' => 'boolean',
        'jack_present' => 'boolean',
        'life_saver_present' => 'boolean',
        'first_aid_kit_present' => 'boolean',
        'spare_wheel_absent' => 'boolean',
        'wheel_spanner_absent' => 'boolean',
        'jack_absent' => 'boolean',
        'life_saver_absent' => 'boolean',
        'first_aid_kit_absent' => 'boolean',
    ];

    /**
     * Get the user who submitted this inspection
     */
    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Check if all required items are accounted for (either present or absent)
     */
    public function isComplete(): bool
    {
        $items = ['spare_wheel', 'wheel_spanner', 'jack', 'life_saver', 'first_aid_kit'];
        
        foreach ($items as $item) {
            $present = $this->{$item . '_present'};
            $absent = $this->{$item . '_absent'};
            
            // Each item must be marked as either present OR absent (but not both, and not neither)
            if (!($present || $absent) || ($present && $absent)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get summary of inspection results
     */
    public function getSummary(): array
    {
        $items = ['spare_wheel', 'wheel_spanner', 'jack', 'life_saver', 'first_aid_kit'];
        $summary = [
            'present' => [],
            'absent' => [],
            'incomplete' => []
        ];
        
        foreach ($items as $item) {
            $present = $this->{$item . '_present'};
            $absent = $this->{$item . '_absent'};
            
            if ($present && !$absent) {
                $summary['present'][] = $item;
            } elseif ($absent && !$present) {
                $summary['absent'][] = $item;
            } else {
                $summary['incomplete'][] = $item;
            }
        }
        
        return $summary;
    }
}