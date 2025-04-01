<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class User extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'password',
        'full_name',
        'mobile',
        'address',
        'avatarURL',
        'department',
        'position',
        'hire_date',
        'status',
        'role',
        'isDelete'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'hire_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'isDelete' => 'boolean',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => 'Active',
        'isDelete' => false,
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Generate random ID before creating a new user
        static::creating(function ($user) {
            if (empty($user->id)) {
                $user->id = Str::random(16);
            }
            
            if (empty($user->created_at)) {
                $user->created_at = now();
            }
            
            if (empty($user->updated_at)) {
                $user->updated_at = now();
            }
        });

        static::updating(function ($user) {
            $user->updated_at = now();
        });
    }
    
    /**
     * Get the attendance records for the user.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'user_id', 'id');
    }
    
    /**
     * Get the projects managed by the user.
     */
    public function managedProjects()
    {
        return $this->hasMany(Project::class, 'manager_id', 'id');
    }
    
    /**
     * Get the projects where user is a member.
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_members', 'user_id', 'project_id')
            ->withPivot('role')
            ->withTimestamp('join_at')
            ->wherePivot('isDelete', false);
    }
    
    /**
     * Get the user's tasks.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'user_id', 'id');
    }
    
    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active')->where('isDelete', false);
    }
    
    /**
     * Check if the user is an admin.
     */
    public function isAdmin()
    {
        return $this->role === 'Admin';
    }
    
    /**
     * Check if the user is a manager.
     */
    public function isManager()
    {
        return $this->role === 'Manager';
    }
}