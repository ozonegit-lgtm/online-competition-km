<?php

namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Database\Eloquent\Relations\HasOne;

    class Submission extends Model
    {
        use HasFactory;

        /**
         * ตารางที่ใช้งาน
         */
        protected $table = 'submissions';

        /**
         * Primary Key
         */
        protected $primaryKey = 'id';

        /**
         * Mass Assignment
         */
        protected $fillable = [
            'competition_id',
            'submission_code',
            'project_title',
            'project_description',
            'team_name',
            'contact_name',
            'contact_email',
            'contact_phone',
            'final_score',
            'status',
            'submitted_at',
        ];

        /**
         * Type Casting
         */
        protected $casts = [
            'final_score' => 'decimal:2',
            'submitted_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];

        /**
         * การแข่งขัน
         */
        public function competition(): BelongsTo
        {
            return $this->belongsTo(Competition::class);
        }

        /**
         * สมาชิกในทีม
         */
        public function members(): HasMany
        {
            return $this->hasMany(SubmissionMember::class);
        }

        /**
         * ไฟล์แนบ
         */
        public function files(): HasMany
        {
            return $this->hasMany(SubmissionFile::class);
        }

        /**
         * ข้อมูลจาก Dynamic Form
         */
        public function fieldValues(): HasMany
        {
            return $this->hasMany(SubmissionFieldValue::class);
        }

        /**
         * คะแนน
         */
        public function scores(): HasMany
        {
            return $this->hasMany(Score::class);
        }

        /**
         * รายการเชื่อมรางวัล
         *
         * คืนค่า SubmissionAward
         */
        public function awards(): HasMany
        {
            return $this->hasMany(SubmissionAward::class);
        }


        public function knowledgeItem(): HasOne
        {
            return $this->hasOne(KnowledgeItem::class);
        }
    }