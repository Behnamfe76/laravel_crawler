<?php

namespace App\Http\Controllers\v1;

use App\Models\Job;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\FailedJobs;

class JobController extends Controller
{
    public function index(): \Inertia\Response
    {
        $failedJobs = FailedJobs::paginate(12)->toArray();
        $failedJobsData = array_map(function ($job) {

            $payload = $job['payload'];
            return [
                'id' => $job['id'],
                'uuid' => $job['uuid'],
                'exception' => $job['exception'],
                'queue' => $job['queue'],
                'job' => $payload['displayName'],
                'data' => unserialize($payload['data']['command'])->getData(),
            ];
        }, $failedJobs['data']);
        $failedJobs['data'] = $failedJobsData;

        $jobs = Job::paginate(12)->toArray();
        $jobsData = array_map(function ($job) {
            $payload = $job['payload'];
            return [
                'id' => $job['id'],
                'queue' => $job['queue'],
                'attempts' => $job['attempts'],
                'job' => $payload['displayName'],
                'data' => unserialize($payload['data']['command'])->getData(),
            ];
        }, $jobs['data']);
        $jobs['data'] = $jobsData;

        return Inertia::render('Jobs/Index', [
            'jobs' => $jobs,
            'failedJobs' => $failedJobs,
        ]);
    }

    public function checkJobStatus(Request $request)
    {
        $request->validate([
            'generatedSlug' => 'required|string',
        ]);
        $generatedSlug = $request->get('generatedSlug');

        // Check if the job is in the queue
        $queuedJob = Job::all()->first(function ($job) use ($generatedSlug) {
            $command = unserialize($job->payload['data']['command']);
            return $command->getData()['generated_slug'] === $generatedSlug;
        });

        // Check if the job is in the failed jobs table
        $failedJob = FailedJobs::all()->first(function ($job) use ($generatedSlug) {
            $command = unserialize($job->payload['data']['command']);
            return $command->getData()['generated_slug'] === $generatedSlug;
        });

        // Determine job status
        if ($queuedJob) {
            $status = 'queued';
        } elseif ($failedJob) {
            $status = 'failed';
        } else {
            $status = 'completed or running';
        }

        return response()->json(
            [
                'generated_slug' => $generatedSlug,
                'status' => $status,
            ]
        );
    }
}
