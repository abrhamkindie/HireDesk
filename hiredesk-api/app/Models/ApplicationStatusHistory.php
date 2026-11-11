<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
 

class ApplicationStatusHistory extends Model
{
    protected $table = 'application_status_history';

    use HasFactory ;

    protected $fillable = [
        'application_id',
        'old_status',
        'new_status',
        'changed_by',
        'note',
    ];
    
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

        public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
