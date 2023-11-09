<?php
/*
 * Ripple  Project
 *
 * @author        Enfu Guo
 * @copyright     NPO Organization uec support
 * @link          http://uecsupport.dip.jp/
 * @license       http://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */

App::uses("AppController", "Controller");
App::uses("RecordsQuestion", "RecordQuestion");
App::uses("UsersGroup", "UsersGroup");
App::uses("Course", "Course");
App::uses("User", "User");
App::uses("Group", "Group");
App::uses("Enquete", "Enquete");
App::uses("Attendance", "Attendance");
App::uses("Date", "Date");
App::uses("Lesson", "Lesson");

class AttendancesController extends AppController
{
    public $helpers = ["Html", "Form"];

    public $components = ["Paginator", "Search.Prg"];

    //public $presetVars = true;

    public $paginate = [
        "maxLimit" => 1000,
    ];

    public $presetVars = [
        [
            "name" => "name",
            "type" => "value",
            "field" => "User.name",
        ],
        [
            "name" => "username",
            "type" => "like",
            "field" => "User.username",
        ],
    ];

    public function edit($attendance_id)
    {
        $attendance_date = $this->Attendance->findAttendanceDate(
            $attendance_id,
            "m月d日"
        );
        $this->set("attendance_date", $attendance_date);

        $data = $this->Attendance->find("first", [
            "fields" => ["status", "reason"],
            "conditions" => [
                "id" => $attendance_id,
            ],
            "recursive" => -1,
        ]);
        $attendance_status = $data["Attendance"]["status"];
        $attendance_reason = $data["Attendance"]["reason"];
        $this->set("attendance_status", $attendance_status);
        $this->set("attendance_reason", $attendance_reason);

        if ($this->request->is("post")) {
            $request_data = $this->request->data;
            $edited_status = $request_data["Attendance"]["status"];
            if ($edited_status == 1 or $edited_status == 2) {
                $edited_reason = null;
            } else {
                $edited_reason = $request_data["Attendance"]["reason"];
            }

            $this->Attendance->read(null, $attendance_id);
            $this->Attendance->set([
                "status" => $edited_status,
                "reason" => $edited_reason,
            ]);
            if ($this->Attendance->save()) {
                $this->Flash->success(__("出欠連絡を完了しました。"));
                return $this->redirect([
                    "controller" => "users_courses",
                    "action" => "index",
                ]);
            }
            $this->Flash->error(
                __("出欠連絡に失敗しました、もう一度お試しください。")
            );
        }
    }

    // 授業コード入力（オンライン授業時のみ）
    public function lesson_code()
    {
        $this->loadModel("Date");
        $this->loadModel("Lesson");

        $user_id = $this->Auth->user("id");
        $role = $this->Auth->user("role");
        $today_date_id = $this->Date->getTodayClassId();
        $today_attendance_info = $this->Attendance->find("first", [
            "conditions" => [
                "user_id" => $user_id,
                "date_id" => $today_date_id,
            ],
            "recursive" => -1,
        ]);
        $save_info = $today_attendance_info["Attendance"];

        if ($role != "user") {
            // 受講生ではない
            $this->Flash->error(
                __("授業コードを入力できるのは受講生のみです。")
            );
            return $this->redirect([
                "controller" => "users_courses",
                "action" => "index",
            ]);
        } elseif (!$this->Date->isClassDate()) {
            // 今日は授業日ではない
            $this->Flash->error(__("今日は授業はありません。"));
            return $this->redirect([
                "controller" => "users_courses",
                "action" => "index",
            ]);
        } elseif (!$this->Date->isOnlineClass()) {
            // オンライン授業ではない
            $this->Flash->error(__("今日は通常授業です。"));
            return $this->redirect([
                "controller" => "users_courses",
                "action" => "index",
            ]);
        } elseif ($save_info["status"] == 1) {
            // すでに出席済み
            $this->Flash->error(__("すでに出席済みです。"));
            return $this->redirect([
                "controller" => "users_courses",
                "action" => "index",
            ]);
        }

        if ($this->request->is("post")) {
            $request_data = $this->request->data;
            $input_code = $request_data["Attendance"]["lesson_code"];

            if ($this->Lesson->checkLessonCode($input_code)) {
                $save_info["status"] = 1;

                $login_time = date("Y-m-d H:i:s");
                $save_info["login_time"] = $login_time;
                $save_info["late_time"] = $this->Attendance->calcLateTime(
                    $today_date_id,
                    $login_time
                );

                if ($this->Attendance->save($save_info)) {
                    $this->Flash->success(__("授業コードを受け付けました。"));
                    return $this->redirect([
                        "controller" => "users_courses",
                        "action" => "index",
                    ]);
                }
                $this->Flash->error(
                    __(
                        "授業コードの受け付けに失敗しました。もう一度お試しください。"
                    )
                );
            } else {
                $this->Flash->error(
                    __(
                        "授業コードが違います。コードを確認して、もう一度お試しください。"
                    )
                );
            }
        }
    }

    public function admin_index()
    {
        $this->loadModel("User");
        $this->loadModel("Date");

        $period1_members = $this->User->findAllUserInfoInPeriod(0);
        $period2_members = $this->User->findAllUserInfoInPeriod(1);
        $this->set(compact("period1_members", "period2_members"));

        $attendance_list = $this->Attendance->findAllUserAttendances(6);
        $date_list = $this->Date->getDateListUntilToday("m月d日", 6);
        $this->set("attendance_list", $attendance_list);
        $this->set("date_list", $date_list);

        $future_attendance_list = $this->Attendance->findAllUserFutureAttendances(6);
        $future_date_list = $this->Date->getDateListFromTomorrow("m月d日", 6);
        $this->set("future_attendance_list", $future_attendance_list);
        $this->set("future_date_list", $future_date_list);

        $last_day = $this->Date->getLastClassDate("Y-m-d");

        $last_class_date_id = $this->Date->getLastClassId();
        $rows = $this->Attendance->find("all", [
            "conditions" => [
                "role" => "user",
                "date_id" => $last_class_date_id,
            ],
            "order" => "User.username ASC",
        ]);

        $now = 1;
        $abs_1 = $att_1 = $abs_2 = $att_2 = $cnt_1 = $cnt_2 = 0;
        foreach ($rows as $row) {
            $now = $row["User"]["period"];
            if ($now == 0) {
                if ($row["Attendance"]["status"] == 1) {
                    $att_1++;
                } else {
                    $abs_1++;
                }
                $cnt_1++;
            } else {
                if ($row["Attendance"]["status"] == 1) {
                    $att_2++;
                } else {
                    $abs_2++;
                }
                $cnt_2++;
            }
        }

        $this->set(
            compact(
                "abs_1",
                "abs_2",
                "att_1",
                "att_2",
                "cnt_1",
                "cnt_2",
                "last_day"
            )
        );
    }

    /**
     * @param int $user_id ユーザID
     * 受講生個人の全出欠記録を表示
     */
    public function admin_student_view($user_id)
    {
        $this->loadModel("User");
        $user_info = $this->User->findUserInfo($user_id);
        $this->set("user_info", $user_info);

        $attendance_data = $this->Attendance->getAllTimeAttendances($user_id);
        $this->set("attendance_data", $attendance_data);
    }

    public function admin_edit($user_id, $attendance_id)
    {
        $this->loadModel("User");
        $name = $this->User->find("first", [
            "fields" => ["id", "name"],
            "conditions" => [
                "id" => $user_id,
            ],
            "recursive" => -1,
        ])["User"]["name"];
        $this->set("name", $name);

        $date = $this->Attendance->findAttendanceDate($attendance_id, "m月d日");
        $this->set("date", $date);

        $data = $this->Attendance->find("first", [
            "fields" => ["status", "reason"],
            "conditions" => [
                "id" => $attendance_id,
            ],
            "recursive" => -1,
        ]);
        $attendance_status = $data["Attendance"]["status"];
        $attendance_reason = $data["Attendance"]["reason"];
        $this->set("attendance_status", $attendance_status);
        $this->set("attendance_reason", $attendance_reason);

        $login_time = $this->Attendance->findLoginTime($attendance_id);
        $this->set("login_time", $login_time);

        if ($this->request->is("post")) {
            $request_data = $this->request->data;
            $edited_status = $request_data["Attendance"]["status"];
            $edited_reason = $request_data["Attendance"]["reason"];
            if ($edited_status == 0 or $edited_status == 2) {
                // 欠席または未定
                $edited_login_time = null;
                $late_time = null;
            } else {
                $edited_hour =
                    $request_data["Attendance"]["edited_login_time"]["hour"];
                $edited_minute =
                    $request_data["Attendance"]["edited_login_time"]["min"];
                $login_date = $this->Attendance->findAttendanceDate(
                    $attendance_id
                );
                $edited_login_time =
                    $login_date .
                    " " .
                    $edited_hour .
                    ":" .
                    $edited_minute .
                    ":00";

                $date_id = $this->Attendance->find("first", [
                    "fields" => ["date_id"],
                    "conditions" => ["id" => $attendance_id],
                    "recursive" => -1,
                ])["Attendance"]["date_id"];
                $late_time = $this->Attendance->calcLateTime(
                    $date_id,
                    $edited_login_time
                );

                if ($late_time === null) {
                    $this->Flash->error(
                        __(
                            "ログイン時間がどの時限とも整合しません。ログイン時間を確認してください。また、授業時間が正しく設定されているか確認してください。"
                        )
                    );
                    return;
                }
            }

            $this->Attendance->read(null, $attendance_id);
            $this->Attendance->set([
                "status" => $edited_status,
                "reason" => $edited_reason,
                "login_time" => $edited_login_time,
                "late_time" => $late_time,
            ]);
            if ($this->Attendance->save()) {
                $this->Flash->success(__("編集が完了しました。"));
                return $this->redirect(["action" => "index"]);
            }
            $this->Flash->error(
                __("編集に失敗しました、もう一回やってください。")
            );
        }
    }

    public function admin_attendance_status()
    {
        $this->loadModel("Attendance");
        $this->loadModel("Date");

        $last_day = $this->Date->getLastClassDate("Y-m-d");
        $last_class_date_id = $this->Date->getLastClassId();

        $this->set(compact("last_day", "last_class_date_id"));

        $period_1_attended = $this->Attendance->findAttendedUsersTheDateThePeriod($last_class_date_id, 0);
        $period_1_absent = $this->Attendance->findAbsentUsersTheDateThePeriod($last_class_date_id, 0);

        $this->set("period_1_submitted", $period_1_attended);
        $this->set("period_1_unsubmitted", $period_1_absent);

        $period_2_attended = $this->Attendance->findAttendedUsersTheDateThePeriod($last_class_date_id, 1);
        $period_2_absent = $this->Attendance->findAbsentUsersTheDateThePeriod($last_class_date_id, 1);

        $this->set("period_2_submitted", $period_2_attended);
        $this->set("period_2_unsubmitted", $period_2_absent);
    }
}
?>
