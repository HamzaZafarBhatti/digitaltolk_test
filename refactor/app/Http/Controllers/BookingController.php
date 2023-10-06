<?php

namespace DTApi\Http\Controllers;

use App\Traits\ResponseTrait;
use DTApi\Models\Job;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{
    use ResponseTrait;
    /**
     * BookingController constructor.
     * @param BookingRepository $repository
     */
    public function __construct(protected BookingRepository $repository)
    {
    }

    /**
     * @param Request $request
     */
    public function index(Request $request)
    {
        if (in_array(auth()->user()->user_type, [config('roles.admin_role_id'), config('roles.superadmin_role_id')])) {
            $jobs = $this->repository->getAll($request);
            return self::success(data: $jobs);
        }

        $jobs = $this->repository->getUsersJobs($request->user_id);
        return self::success(data: $jobs);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show(Job $job)
    {
        $job->load('translatorJobRel.user');

        return self::success(data: $job);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function store(StoreJobRequest $request)
    {
        $data = $request->validated();
        try {
            $response = $this->repository->store($data);
            return self::success(data: $response, code: 201);
        } catch (\Throwable $th) {
            return self::error(message: $th->getMessage());
        }
    }

    /**
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function update(Job $job, UpdateJobRequest $request)
    {
        $data = $request->validated();
        $cuser = auth()->user();
        try {
            $response = $this->repository->updateJob($job, $data, $cuser);
            return self::success(data: $response);
        } catch (\Throwable $th) {
            return self::error(message: $th->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function immediateJobEmail(JobEmailRequest $request)
    {
        $data = $request->validated();
        try {
            $response = $this->repository->storeJobEmail($data);
            return self::success(data: $response);
        } catch (\Throwable $th) {
            return self::error(message: $th->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getHistory(Request $request)
    {
        if ($user_id = $request->get('user_id')) {
            $response = $this->repository->getUsersJobsHistory($user_id, $request);
            return self::success(data: $response);
        }
        return self::success(code: 204);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function acceptJob(AcceptJobRequest $request)
    {
        $data = $request->validated();
        $user = auth()->user();
        try {
            $response = $this->repository->acceptJob($data, $user);
            return self::success(data: $response);
        } catch (\Throwable $th) {
            return self::error(message: $th->getMessage());
        }
    }

    public function acceptJobWithId(Request $request)
    {
        $data = $request->get('job_id');
        $user = auth()->user();
        try {
            $response = $this->repository->acceptJobWithId($data, $user);
            return self::success(data: $response);
        } catch (\Throwable $th) {
            return self::error(message: $th->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function cancelJob(CancelJobRequest $request)
    {
        $data = $request->validated();
        $user = auth()->user();
        try {
            $response = $this->repository->cancelJobAjax($data, $user);
            return self::success(data: $response);
        } catch (\Throwable $th) {
            return self::error(message: $th->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function endJob(EndJobRequest $request)
    {
        $data = $request->validated();
        try {
            $response = $this->repository->endJob($data);
            return self::success(data: $response);
        } catch (\Throwable $th) {
            return self::error(message: $th->getMessage());
        }
    }

    public function customerNotCall(CustomerNotCallRequest $request)
    {
        $data = $request->validated();
        try {
            $response = $this->repository->customerNotCall($data);
            return self::success(data: $response);
        } catch (\Throwable $th) {
            return self::error(message: $th->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getPotentialJobs()
    {
        $user = auth()->user();
        $response = $this->repository->getPotentialJobs($user);
        return self::success(data: $response);
    }

    function checkValue($value)
    {
        return empty($value) ? '' : $value;
    }

    public function distanceFeed(DistanceFeedRequest $request)
    {
        $data = $request->validated();
        if ($data['flagged'] == 'true' && $data['admincomment'] == '') {
            return self::error(message: "Please, add comment", code: 400);
        }
        $distance = $this->checkValue($data['distance']);
        $time = $this->checkValue($data['time']);
        $jobid = $this->checkValue($data['jobid']);
        $session = $this->checkValue($data['session_time']);
        $admincomment = $this->checkValue($data['admincomment']);
        $manually_handled = $data['manually_handled'] == 'true' ? 'yes' : 'no';
        $by_admin = $data['by_admin'] == 'true' ? 'yes' : 'no';
        $flagged = $data['flagged'] == 'true' ? 'yes' : 'no';
        try {
            if ($time || $distance) {
                Distance::where('job_id', $jobid)->update([
                    'distance' => $distance,
                    'time' => $time
                ]);
            }
            if ($admincomment || $session || $flagged || $manually_handled || $by_admin) {
                Job::where('id', $jobid)->update([
                    'admin_comments' => $admincomment,
                    'flagged' => $flagged,
                    'session_time' => $session,
                    'manually_handled' => $manually_handled,
                    'by_admin' => $by_admin
                ]);
            }
            return self::success(message: 'Record updated!');
        } catch (\Throwable $th) {
            return self::error(message: $th->getMessage());
        }
    }

    public function reopen(ReopenRequest $request)
    {
        $data = $request->validated();
        try {
            $response = $this->repository->reopen($data);
            return self::success(data: $response);
        } catch (\Throwable $th) {
            return self::error(message: $th->getMessage());
        }
    }

    public function resendNotifications(ResendNotificationRequest $request)
    {
        $data = $request->validated();
        $job = $this->repository->find($data['jobid']);
        $job_data = $this->repository->jobToData($job);
        try {
            $this->repository->sendNotificationTranslator($job, $job_data, '*');
            return self::success(message: 'Push sent');
        } catch (\Throwable $th) {
            return self::error(message: $th->getMessage());
        }
    }

    /**
     * Sends SMS to Translator
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendSMSNotifications(ResendSMSNotificationsRequest $request)
    {
        $data = $request->validated();
        $job = $this->repository->find($data['jobid']);
        try {
            $this->repository->sendSMSNotificationToTranslator($job);
            return self::success(message: 'SMS sent');
        } catch (\Throwable $th) {
            return self::error(message: $th->getMessage());
        }
    }
}
