<?php
namespace App\Notifications\Models;

use App\Users\Models\User;
use App\Labels\Models\Label;
use Micro\Helpers\Markdown;

class Notification extends \App\Tasks\Models\TaskActivity {

    public static function items($start = NULL, $limit = NULL) {
        $app = \Micro\App::getDefault();
        $dev = $app->request->getQuery('device');
        $auth = $app->auth->user();

        $query = self::get()
            ->alias('a')
            ->join('App\Tasks\Models\Task', 'b.tt_id = a.tta_tt_id', 'b', 'left') 
            ->join('App\Projects\Models\Project', 'c.sp_id = b.tt_sp_id', 'c', 'left')
            ->join('App\Projects\Models\ProjectUser', 'd.spu_sp_id = c.sp_id', 'd', 'left')
            ->where('d.spu_su_id = :user:', array('user' => $auth['su_id']))
            ->orderBy('a.tta_created DESC');

        if ( ! is_null($start) && ! is_null($limit)) {
            $query->limit($limit, $start);
            $result = $query->paginate(FALSE);
        } else {
            $result = $query->paginate();
        }

        if ($dev == 'mobile') {
            $result->map(function($row){
                $data = $row->toArray();
                $data['tta_html'] = Markdown::html($data['tta_verb']);
                return $data;
            });
        }

        return $result;
    }

    public function toArray($columns = NULL) {
        $data = array();
        $time = $this->getRelativeTime();
        $time = str_replace(array('about ', 'at '), '', $time);
        $icon = $this->getIcon();

        $data['tta_verb'] = $this->getVerb();
        $data['tta_time'] = ucfirst($time);
        $data['tta_icon'] = $icon;
        $data['tta_tt_id'] = $this->tta_tt_id;
        $data['tta_link'] = $this->getLink();

        return $data;
    }

    // @Override
    public function getVerb() {
        $verb = '';
        $type = $this->tta_type;
        $time = $this->getRelativeTime();
        $task = $this->task;

        $sender = $this->tta_sender;
        $sender_name = '';

        if ($this->sender) {
            $sender_name = $this->sender->getName();
        }

        switch($type) {
            case 'comment':
                $verb = sprintf('**%s** mengomentari aktivitas: "%s"', $sender_name, $task->tt_title);
                break;
            case 'create':
                $verb = sprintf('**%s** membuat aktivitas: "%s"', $sender_name, $task->tt_title );
                break;
            case 'update':
            case 'update_title':
                $verb = sprintf('**%s** merubah judul aktivitas: "%s"', $sender_name, $task->tt_title );
                break;
            case 'update_detail':
                $verb = sprintf('**%s** merubah detail aktivitas: "%s"', $sender_name, $task->tt_title );
                break;
            case 'update_flag':
                $verb = sprintf(
                    '**%s** merubah status menjadi **%s** pada aktivitas: "%s"',
                    $sender_name,
                    $this->tta_data,
                    $task->tt_title
                );
                break;
            case 'update_due':
                $verb = sprintf(
                    '**%s** merubah due date menjadi **%s** pada aktivitas: "%s"',
                    $sender_name,
                    self::_formatDate($this->tta_data, 'M d, Y'),
                    $task->tt_title
                );
                break;
            case 'add_user':
            case 'remove_user':

                $assignee = '';

                if ( ! empty($this->tta_data)) {
                    $keys = json_decode($this->tta_data);
                    $data = User::get()
                        ->columns('su_id, su_fullname, su_email')
                        ->inWhere('su_id', $keys)
                        ->execute();

                    $count = 1;
                    $total = count($data);

                    foreach($data as $e) {
                        if ($sender == $e->su_id) {
                            $name = 'dirinya sendiri';
                        } else {
                            $name = empty($e->su_fullname) ? $e->su_email : $e->su_fullname;    
                        }

                        if ($total > 1 && $total == $count) {
                            $assignee .= " dan **$name**";
                        } else {
                            $assignee .= ", **$name**";    
                        }

                        $count++;
                    }

                    $assignee = substr($assignee, 2);

                }

                $action = $type == 'add_user' ? 'menugaskan' : 'menghapus';

                $verb = sprintf(
                    '**%s** %s %s kedalam aktivitas: "%s"',
                    $sender_name,
                    $action,
                    $assignee,
                    $task->tt_title
                );
                break;
            case 'add_label':
            case 'remove_label':

                $labels = array();
                $plural = 'label';

                if ( ! empty($this->tta_data)) {
                    $data = Label::get()
                        ->columns('sl_id, sl_label, sl_color')
                        ->inWhere('sl_id', json_decode($this->tta_data))
                        ->execute();

                    foreach($data as $e) {
                        $labels[] = '<span style="font-weight: 500; color: '.$e->sl_color.'">'.$e->sl_label.'</span>';
                    }

                    $plural = count($labels) > 1 ? 'label' : 'label';
                    $labels = implode('&nbsp;', $labels);
                }

                $action = $type == 'add_label' ? 'menambah' : 'menghapus';

                $verb = sprintf(
                    '**%s** %s %s %s untuk aktivitas: "%s"',
                    $sender_name,
                    $action,
                    $plural,
                    $labels,
                    $task->tt_title
                );

                break;
        }

        return $verb;
    }
}