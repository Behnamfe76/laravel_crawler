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
            $payload = json_decode($job['payload'], true);
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
            $payload = json_decode($job['payload'], true);
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
}
