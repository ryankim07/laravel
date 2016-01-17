<?php namespace App\Helpers;

/**
 * Class Email
 *
 * Helper
 *
 * @author     Ryan Kim
 * @category   Mophie
 * @package    Test Planner
 * @copyright  Copyright (c) 2016 mophie (https://lpp.nophie.com)
 */

use App\Facades\Utils;

use Mail;
use Config;

class Email
{
    /**
     * Send email
     *
     * @param $type
     * @param $data
     * @return mixed
     */
    public function sendEmail($type, $data)
    {
        // Type of email to be send out
        switch($type) {
            case 'plan-created':
                $emailSubject = config('mail.plan_created_subject') . ' - ' . $data['description'];
                $emailType    = 'emails.plan_created';
            break;

            case 'ticket-response':
                $emailSubject =  $data['description'] . ' - ' . config('mail.ticket_response_subject') . ' ' . $data['tester_first_name'];
                $emailType    = 'emails.ticket_response';
                break;
        }

        // Process email
        try {
            switch($emailType) {
                case 'emails.plan_created':
                    if (count($data['testers']) > 1) {
                        // Multiple testers
                        foreach ($data['testers'] as $tester) {
                            Mail::send($emailType, array_merge($data, $tester), function ($message) use ($tester, $emailSubject) {
                                $message->to($tester['email'], $tester['first_name'])->subject($emailSubject);
                            });
                        }
                    } else {
                        // Single tester
                        $tester = array_shift($data['testers']);
                        Mail::send($emailType, array_merge($data, $tester), function ($message) use ($tester, $emailSubject) {
                            $message->to($tester['email'], $tester['first_name'])->subject($emailSubject);
                        });
                    }
                    break;

                case 'emails.ticket_response':
                    Mail::send($emailType, $data, function ($message) use ($data, $emailSubject) {
                        $message->from($data['tester_email'], $data['tester_first_name']);
                        $message->to($data['creator_email'], $data['creator_first_name'])->subject($emailSubject);
                    });
                break;
            }
        } catch(\Exception $e) {
            Utils::log($e->getMessage(), $data);
        }

        return true;
    }
}