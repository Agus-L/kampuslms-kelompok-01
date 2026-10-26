<?php

namespace Tests\Feature;

use App\Events\SubmissionGraded;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\SubmissionGradedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

class NotificationFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_notifications(): void
    {
        $response = $this->get('/notifications');
        $response->assertRedirect('/login');
    }

    public function test_user_can_view_notifications_page(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);

        $response = $this->actingAs($user)->get('/notifications');

        $response->assertStatus(200);
        $response->assertSee('Pusat Notifikasi');
    }

    public function test_user_can_mark_their_own_notification_as_read(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);

        $notification = $user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => 'App\Notifications\AssignmentPublishedNotification',
            'data' => [
                'course_name' => 'Web Dev',
                'message' => 'Tugas baru telah dibuat',
                'action_url' => '/mahasiswa/courses/1/assignments',
            ],
            'read_at' => null,
        ]);

        $this->assertNull($notification->fresh()->read_at);

        $response = $this->actingAs($user)->patch("/notifications/{$notification->id}");

        $response->assertRedirect();
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_mark_other_users_notification_as_read_idor_prevention(): void
    {
        $alice = User::factory()->create(['role' => 'mahasiswa']);
        $bob = User::factory()->create(['role' => 'mahasiswa']);

        $aliceNotification = $alice->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => 'App\Notifications\AssignmentPublishedNotification',
            'data' => [
                'course_name' => 'Web Dev',
                'message' => 'Tugas rahasia Alice',
            ],
            'read_at' => null,
        ]);

        // Bob tries to mark Alice's notification as read
        $response = $this->actingAs($bob)->patch("/notifications/{$aliceNotification->id}");

        $response->assertStatus(403);
        $this->assertNull($aliceNotification->fresh()->read_at);
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);

        for ($i = 0; $i < 3; $i++) {
            $user->notifications()->create([
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'type' => 'App\Notifications\AssignmentPublishedNotification',
                'data' => [
                    'course_name' => 'Web Dev',
                    'message' => "Tugas ke-{$i}",
                ],
                'read_at' => null,
            ]);
        }

        $this->assertEquals(3, $user->unreadNotifications()->count());

        $response = $this->actingAs($user)->post('/notifications/mark-all-read');

        $response->assertRedirect();
        $this->assertEquals(0, $user->fresh()->unreadNotifications()->count());
    }

    public function test_submission_graded_notification_payload_does_not_contain_score_or_feedback(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

        $course = Course::factory()->create(['lecturer_id' => $dosen->id]);
        $assignment = Assignment::factory()->create(['course_id' => $course->id, 'max_score' => 100]);
        $submission = Submission::factory()->create(['assignment_id' => $assignment->id, 'user_id' => $mahasiswa->id]);
        $grade = Grade::factory()->create([
            'submission_id' => $submission->id,
            'graded_by' => $dosen->id,
            'score' => 95.5,
            'feedback' => 'Sangat rapi dan memuaskan',
        ]);

        $notification = new SubmissionGradedNotification($grade);
        $payload = $notification->toArray($mahasiswa);

        // Assert sensitive data is NOT stored in payload
        $this->assertArrayNotHasKey('score', $payload, 'Payload must not contain score');
        $this->assertArrayNotHasKey('feedback', $payload, 'Payload must not contain feedback');
        $this->assertArrayHasKey('assignment_title', $payload);
        $this->assertArrayHasKey('course_name', $payload);
    }
}
