<?php
declare(strict_types=1);

namespace App\Controller;

use App\Lib\EvalMath;
use Cake\Event\EventInterface;
use Cake\ORM\TableRegistry;

/**
 * Fields Controller
 *
 * @property \App\Model\Table\FieldsTable $Fields
 */
class FieldsController extends AppController
{
    /**
     * BeforeFilter event handler
     *
     * @param \Cake\Event\EventInterface $event Event interface
     * @return void
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        if (!empty($this->FormProtection)) {
            if (in_array($this->getRequest()->getParam('action'), ['checkFormula'])) {
                $this->FormProtection->setConfig('validate', false);
            }
        }
    }

    /**
     * Checks validity of formula in $_POST['expression']
     *
     * @return \Cake\Http\Response
     */
    public function checkFormula()
    {
        $this->Authorization->skipAuthorization();

        $result = ['result' => false, 'error' => ''];
        if ($this->request->is(['post'])) {
            // local evaluation variables
            $lvars = [];

            $EvalMath = EvalMath::getInstance();

            /** @var \App\Model\Table\ProjectsTable $Projects */
            $Projects = TableRegistry::getTableLocator()->get('Projects');

            // load project variables
            $pvars = (array)$Projects->getVariables($this->request->getData('project_id'));
            foreach ($pvars as $var_name => $var_value) {
                $lvars[$var_name] = $var_value['value'];
            }

            // load variables from POST
            $var_value = $this->parseDataVariable('aux');
            if (!empty($var_value)) {
                $lvars['Aux'] = $var_value;
            }

            // evaluate expression
            $value = $this->request->getData('expression');
            if (substr($value, 0, 1) == '=') {
                $formula_value = '';
                try {
                    $formula_value = $EvalMath->evaluateExpression(substr($value, 1), $lvars);
                } catch (\Exception $e) {
                    $result['error'] = $EvalMath->getError();
                }
                if ($EvalMath->isValidFloat((string)$formula_value)) {
                    $result['result'] = '' . $formula_value;
                }
            }
        }
        $this->response = $this->response->withType('json');
        $this->response = $this->response->withStringBody((string)json_encode($result));

        return $this->response;
    }

    /**
     * Parses data variable with given name
     *
     * @param string $varName Variable name.
     * @return mixed Returns false on error or decimal number on success.
     */
    private function parseDataVariable($varName)
    {
        $ret = false;
        $value = $this->request->getData($varName);
        if (!empty($value)) {
            $EvalMath = EvalMath::getInstance();

            if ($EvalMath->isValidFloat($value)) {
                $ret = $EvalMath->delocalize($value);
            }
        }

        return $ret;
    }
}
