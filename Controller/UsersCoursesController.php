<?php
/**
 * iroha Board Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2016 iroha Soft, Inc. (http://irohasoft.jp)
 * @link          http://irohaboard.irohasoft.jp
 * @license       http://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */

App::uses("AppController", "Controller");

/**
 * UsersCourses Controller
 *
 * @property UsersCourse $UsersCourse
 * @property PaginatorComponent $Paginator
 */
class UsersCoursesController extends AppController
{
    public $components = [];

    /**
     * 受講コース一覧（ホーム画面）を表示
     */
    public function index()
    {
        $this->loadModel("Group");
        $this->loadModel("User");
        $this->loadModel("Attendance");
        $this->loadModel("Date");
        $this->loadModel("Enquete");
        $this->loadModel("Category");
        $this->loadModel("Course");
        $this->loadModel("Content");
        $this->loadModel("Record");
        $this->loadModel("UsersCourse");

        $user_id = $this->Auth->user("id");

        $role = $this->Auth->user("role");
        $this->set("role", $role);

        // 全体のお知らせの取得
        App::import("Model", "Setting");
        $this->Setting = new Setting();

        $data = $this->Setting->find("all", [
            "conditions" => [
                "Setting.setting_key" => "information",
            ],
            "recursive" => -1,
        ]);

        $info = $data[0]["Setting"]["setting_value"];

        // お知らせ一覧を取得
        $this->loadModel("Info");
        $this->loadModel("Course");
        $infos = $this->Info->getInfos($user_id, 2);

        $no_info = "";

        // 全体のお知らせが存在しない場合
        if ($info == "") {
            $no_info = __("お知らせはありません");
        }

        // 次回までのゴールを取得
        $next_goal = $this->Enquete->findCurrentNextGoal($user_id);
        $this->set("next_goal", $next_goal);

        // 受講コース情報の取得
        //$courses = $this->UsersCourse->getCourseRecord($user_id);
        //$all_courses = $this->UsersCourse->getCourseRecord($user_id);
        //$all_courses = [];

        $category_data = $this->Category->find("all", [
            "order" => "Category.sort_no asc",
            "recursive" => -1,
        ]);
        $categories = [];
        foreach ($category_data as $category_datum) {
            array_push($categories, [
                "id" => $category_datum["Category"]["id"],
                "title" => $category_datum["Category"]["title"],
            ]);
        }
        $others_category_id = 0;
        array_push($categories, [
            "id" => $others_category_id,
            "title" => "未分類",
        ]);

        $categories_and_courses = [];
        foreach ($categories as $category) {
            $categories_and_courses[$category["id"]] = [];
        }

        if ($role === "admin") {
            $all_courses = $this->Course->find("all", [
                "fields" => [
                    "Course.category_id", "Course.id", "Course.title", "Course.introduction", "Course.before_course",
                ],
                "order" => [
                    "Course.category_id" => "ASC",
                    "Course.sort_no" => "ASC",
                ],
                "recursive" => -1,
            ]);

            foreach ($all_courses as $admin_course) {
                $category_id = $admin_course["Course"]["category_id"];
                $course_id = $admin_course["Course"]["id"];

                $admin_user_course = $this->UsersCourse->find("first", [
                    "fields" => ["started", "ended", "id"],
                    "conditions" => [
                        "user_id" => $user_id,
                        "course_id" => $course_id,
                    ],
                    "recursive" => -1,
                ]);

                $started = (empty($admin_user_course) || $admin_user_course["UsersCourse"]["started"] == null) ? null : $admin_user_course["UsersCourse"]["started"];
                $ended = (empty($admin_user_course) || $admin_user_course["UsersCourse"]["ended"] == null) ? null : $admin_user_course["UsersCourse"]["ended"];

                $sum_content_cnt = $this->Content->find("count", [
                    "conditions" => [
                        "Content.course_id" => $course_id,
                    ],
                    "recursive" => -1,
                ]);
                $did_content_cnt = $this->Record->find("count", [
                    "conditions" => [
                        "Record.course_id" => $course_id,
                        "Record.user_id" => $user_id,
                    ],
                    "fields" => "DISTINCT Record.content_id",
                    "recursive" => -1,
                ]);

                $course = [
                    "id" => $course_id,
                    "title" => $admin_course["Course"]["title"],
                    "introduction" => $admin_course["Course"]["introduction"],
                    "before_course" => $admin_course["Course"]["before_course"],
                    "first_date" => $started,
                    "last_date" => $ended,
                    "sum_cnt" => $sum_content_cnt,
                    "did_cnt" => $did_content_cnt,
                ];
                if ($category_id == NULL) {
                    array_push($categories_and_courses[$others_category_id], $course);
                } else {
                    array_push($categories_and_courses[$category_id], $course);
                }
            }
        } else {
            $users_courses = $this->UsersCourse->find("all", [
                "fields" => [
                    "Course.category_id", "Course.id", "Course.title", "Course.introduction", "Course.before_course",
                    "UsersCourse.started", "UsersCourse.ended",
                ],
                "conditions" => [
                    "UsersCourse.user_id" => $user_id,
                    "Course.status" => 1,
                ],
                "order" => [
                    "Course.category_id" => "ASC",
                    "Course.sort_no" => "ASC",
                ],
            ]);

            foreach ($users_courses as $users_course) {
                $category_id = $users_course["Course"]["category_id"];
                $course_id = $users_course["Course"]["id"];

                $sum_content_cnt = $this->Content->find("count", [
                    "conditions" => [
                        "Content.course_id" => $course_id,
                    ],
                    "recursive" => -1,
                ]);
                $did_content_cnt = $this->Record->find("count", [
                    "conditions" => [
                        "Record.course_id" => $course_id,
                        "Record.user_id" => $user_id,
                    ],
                    "fields" => "DISTINCT Record.content_id",
                    "recursive" => -1,
                ]);

                $course = [
                    "id" => $course_id,
                    "title" => $users_course["Course"]["title"],
                    "introduction" => $users_course["Course"]["introduction"],
                    "before_course" => $users_course["Course"]["before_course"],
                    "first_date" => $users_course["UsersCourse"]["started"],
                    "last_date" => $users_course["UsersCourse"]["ended"],
                    "sum_cnt" => $sum_content_cnt,
                    "did_cnt" => $did_content_cnt,
                ];
                if ($category_id == NULL) {
                    array_push($categories_and_courses[$others_category_id], $course);
                } else {
                    array_push($categories_and_courses[$category_id], $course);
                }
            }
        }
        $this->set(compact("categories", "categories_and_courses"));

        /*
        $courses = [];
        // 管理者の場合，コースを全部表示
        if ($role === "admin") {
            $courses = $all_courses;
        } else {
            //受講生の場合
            foreach ($all_courses as &$course) {
                $new_course = [];
                foreach ($course["Course"] as $old_course) {
                    //もし,コースが非公開設定になっている場合
                    if ($old_course["status"] == 0) {
                        continue;
                    }

                    $before_course_id = $course["before_course"];
                    $now_course_id = $course["id"];

                    // 前提コースが無いか，既にクリアしたコンテンツが一つ以上ある
                    if (
                        $this->Course->existCleared($user_id, $now_course_id) ||
                        $before_course_id === null
                    ) {
                        array_push($new_course, $old_course);
                    } else {
                        $result = $this->Course->goToNextCourse(
                            $user_id,
                            $before_course_id,
                            $now_course_id
                        );
                        if ($result) {
                            array_push($new_course, $old_course);
                        } else {
                            continue;
                        }
                    }
                }
                $course["Course"] = $new_course;
            }
            $courses = $all_courses;
        }
        */

        $no_record = "";
        /*
        if (count($courses) == 0) {
            $no_record = __("受講可能なコースはありません");
        }
        */
        $this->set(compact("no_record", "info", "infos", "no_info"));
        //$this->set(compact("courses", "no_record", "info", "infos", "no_info"));

        // role == 'user'の出席情報を取る
        if ($role === "user") {
            $user_info = $this->Attendance->getAllTimeAttendances($user_id, 8);
            $this->set(compact("user_info"));
        }

        // 授業日の受講生は今日の授業のゴールを書く
        if ($role === "user" && $this->Date->isClassDate()) {
            $user_ip = $this->request->ClientIp();
            $have_to_write_today_goal = $this->Attendance->takeAttendance(
                $user_id,
                $user_ip
            );
            $this->set("have_to_write_today_goal", $have_to_write_today_goal);

            $today_attendance_id = $this->Attendance->find("first", [
                "fields" => ["Attendance.id"],
                "conditions" => [
                    "Attendance.user_id" => $user_id,
                    "Date.date" => date("Y-m-d"),
                ],
            ])["Attendance"]["id"];
            $this->set("today_attendance_id", $today_attendance_id);

            //グループリストを生成(公開状態のグループのみ)
            $group_list = $this->Group->find("list", [
                "conditions" => [
                    "status" => 1,
                ],
            ]);
            $this->set("group_list", $group_list);
            $group_id = $this->User->findUserGroup($user_id);
            $this->set("group_id", $group_id);
        }
    }
}
