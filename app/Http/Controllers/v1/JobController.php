<?php

namespace App\Http\Controllers\v1;

use App\Models\Job;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class JobController extends Controller
{
    public function index(): \Inertia\Response
    {
        $jobs = Job::paginate(12)->toArray();

        $data = array_map(function ($job) {
                $payload = json_decode($job['payload'], true);
                return [
                    'id' => $job['id'],
                    'queue' => $job['queue'],
                    'attempts' => $job['attempts'],
                    'job' => $payload['displayName'],
                    'data' => unserialize($payload['data']['command'])->getData(),
                ];
            }, $jobs['data']);

        $jobs['data'] = $data;

        return Inertia::render('Jobs/Index', [
            'jobs' => $jobs
        ]);
    }
}
