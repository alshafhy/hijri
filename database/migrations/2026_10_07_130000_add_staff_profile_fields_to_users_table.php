<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone_number', 30)->nullable()->after('email');
            $table->string('membership_number', 100)->nullable()->after('phone_number');
            $table->string('membership_category', 100)->nullable()->after('membership_number');
            $table->string('membership_branch_type', 100)->nullable()->after('membership_category');
            $table->string('signature_path')->nullable()->after('membership_branch_type');
            $table->string('avatar_path')->nullable()->after('signature_path');
            $table->boolean('can_approve_final_request')->default(false)->after('status');
            $table->string('invitation_token', 64)->nullable()->unique()->after('remember_token');
            $table->timestamp('invitation_sent_at')->nullable()->after('invitation_token');
        });

        Schema::create('support_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->string('status', 20)->default('open')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_requests');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['invitation_token']);
            $table->dropColumn([
                'phone_number', 'membership_number', 'membership_category', 'membership_branch_type',
                'signature_path', 'avatar_path', 'can_approve_final_request', 'invitation_token', 'invitation_sent_at',
            ]);
        });
    }
};
