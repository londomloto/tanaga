<?php
namespace App\Bpmn\Controllers;

use Micro\Helpers\Text;

class GeneratorController extends \Micro\Controller {

    public function generateAction($worker) {
        $base = APPPATH.'modules/Workflow/';
        $name = Text::camelize($worker);
        $transModel = $name;
        $transTable = str_replace('-', '_', $worker);
        $statusModel = $name.'Status';
        $statusTable = $transTable.'_statuses';

        //-------------------------------------------------
        // GENERATE CONTROLLER
        //-------------------------------------------------
        $ctrl = $name.'Controller';
        $file = $base.'Controllers/'.$ctrl.'.php';
        $open = fopen($file, 'w');

        $data = <<<DATA
<?php
namespace App\Workflow\Controllers;

use App\Workflow\Models\\$transModel,
    App\Workflow\Models\\$statusModel;

class $ctrl extends \Micro\Controller {
    
    public function findAction() {
        \$params = \$this->request->getQuery();
        \$statuses = isset(\$params['statuses']) ? json_decode(\$params['statuses']) : array();

        if ( ! empty(\$statuses)) {
            \$data = $statusModel::get()
                ->filter(function(\$row){
                    \$trans = \$row->trans;
                    \$status  = \$row->status;

                    \$data = array();
                    \$data['id'] = NULL;
                    \$data['text'] = NULL;
                    \$data['status'] = \$row->id;
                    \$data['status_text'] = \$status ? \$status->label : NULL;
                    \$data['status_date'] = \$row->created;
                    \$data['current'] = \$status ? \$status->source_id : NULL;
                    \$data['target'] = \$row->target;
                    \$data['worker'] = \$row->worker;
                })
                ->execute();

            return array(
                'success' => TRUE,
                'data' => \$data
            );
        }
    }

    public function findByIdAction(\$id) {

    }

    public function createAction() {

    }

    public function updateAction(\$id) {

    }

    public function deleteAction(\$id) {

    }

}
DATA;
    
        fwrite($open, $data);
        fclose($open);

        //-------------------------------------------------
        // GENERATE TRANS MODEL
        //-------------------------------------------------
        $file = $base.'Models/'.$transModel.'.php';
        $open = fopen($file, 'w');

        $data = <<<DATA
<?php
namespace App\Workflow\Models;

class $transModel extends \Micro\Model {

    public function getSource() {
        return '$transTable';
    }

}
DATA;
    
        fwrite($open, $data);
        fclose($open);

        //-------------------------------------------------
        // GENERATE STATUS MODEL
        //-------------------------------------------------

        $file = $base.'Models/'.$statusModel.'.php';
        $open = fopen($file, 'w');

        $data = <<<DATA
<?php
namespace App\Workflow\Models;

class $statusModel extends \Micro\Model {

    public function getSource() {
        return '$statusTable';
    }

}
DATA;
    
        fwrite($open, $data);
        fclose($open);

    }

}