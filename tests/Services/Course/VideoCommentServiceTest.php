<?php

/*
 * This file is part of the Qsnh/meedu.
 *
 * (c) XiaoTeng <616896861@qq.com>
 */

namespace Tests\Services\Course;

use Tests\TestCase;
use App\Services\Member\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Services\Course\Models\Video;
use App\Services\Course\Models\VideoComment;
use App\Services\Course\Services\VideoCommentService;
use App\Services\Member\Services\NotificationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Services\Course\Interfaces\VideoCommentServiceInterface;
use App\Services\Member\Interfaces\NotificationServiceInterface;

class VideoCommentServiceTest extends TestCase
{

    /**
     * @var VideoCommentService
     */
    protected $service;

    /**
     * @var NotificationService
     */
    protected $notificationService;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(VideoCommentServiceInterface::class);
        $this->notificationService = $this->app->make(NotificationServiceInterface::class);
    }

    public function test_create()
    {
        $user = factory(User::class)->create();
        Auth::login($user);
        $video = factory(Video::class)->create();

        $comment = $this->service->create($video->id, '我是评价的内容');

        $this->assertEquals('我是评价的内容', $comment['original_content']);
        $this->assertEquals($user->id, $comment['user_id']);
    }

    public function test_courseComments()
    {
        $video = factory(Video::class)->create();
        $comments = factory(VideoComment::class, 10)->create([
            'video_id' => $video,
            'user_id' => 1,
        ]);

        $list = $this->service->videoComments($video->id);
        $this->assertEquals($comments->count(), count($list));
    }

    public function test_find_with_not_exists()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->find(12);
    }

    public function test_find()
    {
        $comment = factory(VideoComment::class)->create();
        $c = $this->service->find($comment->id);
        $this->assertEquals($comment->original_content, $c['original_content']);
    }
}
