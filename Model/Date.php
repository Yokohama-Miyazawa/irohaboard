<?php
/**
 * Ripple  Project
 *
 * @author        Osamu Miyazawa
 * @copyright     NPO Organization uec support
 * @link          http://uecsupport.dip.jp/
 * @license       http://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */

App::uses("AppModel", "Model");

class Date extends AppModel
{
    // The Associations below have been created with all possible keys, those
    // that are not needed can be removed

    /**
     * hasMany associations
     *
     * @var array
     */
    public $hasMany = [
        "Lesson" => [
            "className" => "Lesson",
            "foreignKey" => "date_id",
            "dependent" => true,
            "conditions" => "",
            "fields" => "",
            "order" => "",
            "limit" => "",
            "offset" => "",
            "exclusive" => "",
            "finderQuery" => "",
            "counterQuery" => "",
        ],
        "Attendance" => [
            "className" => "Attendance",
            "foreignKey" => "date_id",
            "dependent" => true,
            "conditions" => "",
            "fields" => "",
            "order" => "",
            "limit" => "",
            "offset" => "",
            "exclusive" => "",
            "finderQuery" => "",
            "counterQuery" => "",
        ],
    ];

    /**
     * 検索用
     */
    public $actsAs = ["Search.Searchable"];

    public $filterArgs = [];

    public function getDate($date_id, $format_str = "Y-m-d")
    {
        $data = $this->find("first", [
            "fields" => ["id", "date"],
            "conditions" => ["id" => $date_id],
            "recursive" => -1,
        ]);
        $lesson_date = new DateTime($data["Date"]["date"]);
        $formatted_lesson_date = $lesson_date->format($format_str);
        return $formatted_lesson_date;
    }

    // 今日が授業日か判定
    public function isClassDate()
    {
        $today = date("Y-m-d");
        $data = $this->find("first", [
            "fields" => ["id", "date"],
            "conditions" => ["date" => $today],
            "recursive" => -1,
        ]);
        if ($data) {
            return true;
        }
        return false;
    }

    // 今日はオンライン授業か判定
    public function isOnlineClass()
    {
        $today = date("Y-m-d");
        $data = $this->find("first", [
            "fields" => ["id", "online"],
            "conditions" => ["date" => $today],
            "recursive" => -1,
        ]);
        if ($data["Date"]["online"]) {
            return true;
        }
        return false;
    }

    public function getTodayClassId()
    {
        $today = date("Y-m-d");
        $data = $this->find("first", [
            "fields" => ["id"],
            "conditions" => ["date" => $today],
            "recursive" => -1,
        ]);
        $today_class_id = $data["Date"]["id"];
        return $today_class_id;
    }

    public function getLastClassId()
    {
        $today = date("Y-m-d");
        $data = $this->find("first", [
            "fields" => ["id"],
            "conditions" => ["date <= ?" => $today],
            "order" => "date DESC",
            "recursive" => -1,
        ]);
        $last_class_id = $data["Date"]["id"];
        return $last_class_id;
    }

    public function getLastClassDate($format_str = "Y-m-d")
    {
        $today = date("Y-m-d");
        $data = $this->find("first", [
            "fields" => ["date"],
            "conditions" => ["date <= ?" => $today],
            "order" => "date DESC",
            "recursive" => -1,
        ]);
        $last_class_date = (new DateTime($data["Date"]["date"]))->format(
            $format_str
        );
        return $last_class_date;
    }

    /**
     * 今日までの授業日一覧を取得
     * @param int $format_str  日付のフォーマット
     * @param int $limit  データの個数
     */
    public function getDateListUntilToday($format_str = "Y-m-d", $limit = 8)
    {
        $date_list = [];
        $today = date("Y-m-d");
        $data = $this->find("all", [
            "fields" => ["date"],
            "conditions" => ["date <= ?" => $today],
            "order" => "date DESC",
            "limit" => $limit,
            "recursive" => -1,
        ]);
        foreach ($data as $datum) {
            $date_list[] = (new DateTime($datum["Date"]["date"]))->format(
                $format_str
            );
        }
        return $date_list;
    }

    /**
     * 明日以降の授業日一覧を取得
     * @param int $format_str  日付のフォーマット
     * @param int $limit  データの個数
     */
    public function getDateListFromTomorrow($format_str = "Y-m-d", $limit = 8)
    {
        $date_list = [];
        $tomorrow = date("Y-m-d", strtotime("+1 days"));
        $data = $this->find("all", [
            "fields" => ["date"],
            "conditions" => ["date >= ?" => $tomorrow],
            "order" => "date ASC",
            "limit" => $limit,
            "recursive" => -1,
        ]);
        foreach (array_reverse($data) as $datum) {
            $date_list[] = (new DateTime($datum["Date"]["date"]))->format(
                $format_str
            );
        }
        return $date_list;
    }

    public function getDateListUntilNextLecture(
        $format_str = "Y-m-d",
        $limit = 8
    ) {
        $date_list = [];
        $today = date("Y-m-d", strtotime("+6 days"));
        $data = $this->find("all", [
            "fields" => ["date"],
            "conditions" => ["date <= ?" => $today],
            "order" => "date DESC",
            "limit" => $limit,
            "recursive" => -1,
        ]);
        foreach ($data as $datum) {
            $date_list[] = (new DateTime($datum["Date"]["date"]))->format(
                $format_str
            );
        }
        return $date_list;
    }

    public function getDateIDsFromToday()
    {
        $date_ids = [];
        $today = date("Y-m-d");
        $data = $this->find("all", [
            "fields" => ["id"],
            "conditions" => ["date >= ?" => $today],
            "order" => "date DESC",
            "recursive" => -1,
        ]);
        foreach ($data as $datum) {
            $date_ids[] = $datum["Date"]["id"];
        }
        return $date_ids;
    }
}
