<?php declare(strict_types=1);

use srag\asq\AsqGateway;
use srag\asq\UserInterface\Web\Component\Feedback\AnswerFeedbackComponent;
use srag\asq\UserInterface\Web\Component\Feedback\FeedbackComponent;
use srag\asq\UserInterface\Web\Component\Scoring\ScoringComponent;

/**
 * @ilCtrl_isCalledBy ilObjAQSTestGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjAQSTestGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjAQSTestGUI: ilAQSEditQuestionsGUI
 */
class ilObjAQSTestGUI extends ilObjectPluginGUI
{
    const LP_SESSION_ID = 'xaqs_lp_session_state';
    const TAB_EDITQUESTIONS = 'editQuestions';
    const CMD_RUN_TEST = 'runTest';
 
    protected $ctrl;
    protected $tabs;
    public $tpl;
 
    protected function afterConstructor()
    {
        global $ilCtrl, $ilTabs, $tpl, $DIC;
        $this->ctrl = $ilCtrl;
        $this->tabs = $ilTabs;
        $this->tpl = $tpl;
        $this->dic = $DIC;

    }
 
    function performCommand($cmd)
    {
        
        $next_class = $this->ctrl->getNextClass($this);
        switch ($next_class) {
            case strtolower('ilAQSEditQuestionsGUI'):
                $this->forwardGUI(self::TAB_EDITQUESTIONS, 'ilAQSEditQuestionsGUI');
                break;

            default:
                switch ($cmd) {
                    case "editProperties":   // list all commands that need write permission here
                    case "updateProperties":
                    case "saveProperties":
                    //case "showExport":
                        $this->checkPermission("write");
                        $this->$cmd();
                        break;
         
                    case "showContent":   // list all commands that need read permission here
                    case "setStatusToCompleted":
                    case "setStatusToFailed":
                    case "setStatusToInProgress":
                    case "setStatusToNotAttempted":
                        $this->checkPermission("read");
                        $this->$cmd();
                        break;

                    case self::TAB_EDITQUESTIONS:
                        //$this->redirectGUI(self::TAB_EDITQUESTIONS, 'ilAQSEditQuestionsGUI');
                        $this->forwardGUI(self::TAB_EDITQUESTIONS, 'ilAQSEditQuestionsGUI');
                        break;
                    
                    case self::CMD_RUN_TEST:
                        $this->digestQuestionSubmission();
                        break;
                        


                }
        }
    }
    
    private function redirectGUI($cmd, $gui_class_name) {
        $link = $this->ctrl->getLinkTargetByClass(
            [$gui_class_name],
            $cmd,
            "",
            false,
            false
        );
        $this->ctrl->redirectToURL($link);
    }
    private function forwardGUI($tab, $gui_class_name) {
        $this->tabs->activateTab($tab);
        if($gui_class_name === 'ilAQSEditQuestionsGUI') {
            $gui = new $gui_class_name($this->object);
        }
        $this->ctrl->forwardCommand($gui);
    }

 
    /**
     * Get type.
     */
    final function getType()
    {
        return ilAQSTestPlugin::ID;
    }
 
    /**
     * After object has been created -> jump to this command
     */
    function getAfterCreationCmd()
    {
        return "editProperties";
    }
 
    /**
     * Get standard command
     */
    function getStandardCmd()
    {
        return "showContent";
    }
 
//
// DISPLAY TABS
//
 
    /**
     * Set tabs
     */
    function setTabs()
    {
        global $ilCtrl, $ilAccess;
        $this->tabs->addTab(
            self::TAB_EDITQUESTIONS,
            $this->txt(self::TAB_EDITQUESTIONS),
            $this->ctrl->getLinkTargetByClass(
                'ilAQSEditQuestionsGUI', 
                self::TAB_EDITQUESTIONS
            )
        );

        // tab for the "show content" command
        if ($ilAccess->checkAccess("read", "", $this->object->getRefId()))
        {
            $this->tabs->addTab("content", $this->txt("content"), $ilCtrl->getLinkTarget($this, "showContent"));
        }
 
        // standard info screen tab
        $this->addInfoTab();
 
        // a "properties" tab
        if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
        {
            $this->tabs->addTab("properties", $this->txt("properties"), $ilCtrl->getLinkTarget($this, "editProperties"));
            //$this->tabs->addTab("export", $this->txt("export"), $ilCtrl->getLinkTargetByClass("ilexportgui", ""));
        }
 
        // standard permission tab
        $this->addPermissionTab();
        $this->activateTab();
    }
 
    /**
     * Edit Properties. This commands uses the form class to display an input form.
     */
    protected function editProperties()
    {
        $this->tabs->activateTab("properties");
        $form = $this->initPropertiesForm();
        $this->addValuesToForm($form);
        $this->tpl->setContent($form->getHTML());
    }
 
    /**
     * @return ilPropertyFormGUI
     */
    protected function initPropertiesForm() {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->plugin->txt("obj_xtst"));
 
        $title = new ilTextInputGUI($this->plugin->txt("title"), "title");
        $title->setRequired(true);
        $form->addItem($title);
 
        $description = new ilTextInputGUI($this->plugin->txt("description"), "description");
        $form->addItem($description);
 
        $online = new ilCheckboxInputGUI($this->plugin->txt("online"), "online");
        $form->addItem($online);
 
        $form->setFormAction($this->ctrl->getFormAction($this, "saveProperties"));
        $form->addCommandButton("saveProperties", $this->plugin->txt("update"));
 
        return $form;
    }
 
    /**
     * @param $form ilPropertyFormGUI
     */
    protected function addValuesToForm(&$form) {
        $form->setValuesByArray(array(
            "title" => $this->object->getTitle(),
            "description" => $this->object->getDescription(),
            "online" => $this->object->isOnline(),
        ));
    }
 
    /**
     *
     */
    protected function saveProperties() {
        $form = $this->initPropertiesForm();
        $form->setValuesByPost();
        if($form->checkInput()) {
            $this->fillObject($this->object, $form);
            $this->object->update();
            ilUtil::sendSuccess($this->plugin->txt("update_successful"), true);
            $this->ctrl->redirect($this, "editProperties");
        }
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * @param $object ilObjAQSTest
     * @param $form ilPropertyFormGUI
     */
    private function fillObject($object, $form) {
        $object->setTitle($form->getInput('title'));
        $object->setDescription($form->getInput('description'));
        $object->setOnline($form->getInput('online'));
    }
 
    protected function showExport() {
        require_once("./Services/Export/classes/class.ilExportGUI.php");
        $export = new ilExportGUI($this);
        $export->addFormat("xml");
        $ret = $this->ctrl->forwardCommand($export);
 
    }
 
    /**
     * We need this method if we can't access the tabs otherwise...
     */
    private function activateTab() {
        $next_class = $this->ctrl->getCmdClass();
 
        switch($next_class) {
            case 'ilexportgui':
                $this->tabs->activateTab("export");
                break;
        }
 
        return;
    }
 
    private function setStatusToCompleted() {
        $this->setStatusAndRedirect(ilLPStatus::LP_STATUS_COMPLETED_NUM);
    }
 
    private function setStatusAndRedirect($status) {
        global $ilUser;
        $_SESSION[self::LP_SESSION_ID] = $status;
        ilLPStatusWrapper::_updateStatus($this->object->getId(), $ilUser->getId());
        $this->ctrl->redirect($this, $this->getStandardCmd());
    }
 
    protected function setStatusToFailed() {
        $this->setStatusAndRedirect(ilLPStatus::LP_STATUS_FAILED_NUM);
    }
 
    protected function setStatusToInProgress() {
        $this->setStatusAndRedirect(ilLPStatus::LP_STATUS_IN_PROGRESS_NUM);
    }
 
    protected function setStatusToNotAttempted() {
        $this->setStatusAndRedirect(ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM);
    }


    protected function showContent() 
    {
        $this->tabs->activateTab("content");
        $template = $this->plugin->getTemplate("tpl.content.html");
        $action = $this->ctrl->getFormAction($this, self::CMD_RUN_TEST);
        $save_button = ilSubmitButton::getInstance();
        $save_button->setCaption($this->dic->language()->txt('submit_answer'), false);
        $save_button->setCommand(self::CMD_RUN_TEST);

        $template->setVariable("TITLE", $this->object->getTitle());
        $template->setVariable("DESCRIPTION", $this->object->getDescription());
        $template->setVariable("ACTION", $action);
        $template->setVariable("SUBMIT", $save_button->render());

        $this->renderQuiz($template);
        $this->tpl->setContent($template->get());
    }

    protected function renderQuiz($template) 
    {
        $questions = AsqGateway::get()->question()->getQuestionsOfContainer($this->object->getId());
        $q_ui = AsqGateway::get()->ui();

        foreach ($questions as $q) {
            $q_component = $q_ui->getQuestionComponent($q);
            $qdata = $q->getData();
            $template->setCurrentBlock('Q');
            $template->setVariable("QUESTION", $q_component->renderHtml());
            $template->parseCurrentBlock();
        }
    }

    protected function digestQuestionSubmission() 
    {
        $template = $this->plugin->getTemplate("tpl.feedback.html", true);
        
        $this->tabs->activateTab("content");

        $q_ui = AsqGateway::get()->ui();
        $questions = AsqGateway::get()->question()->getQuestionsOfContainer($this->object->getId());
        foreach ($questions as $q) {
            $opt_htm = '';

            $q_component = $q_ui->getQuestionComponent($q);
            $answer = $q_component->readAnswer();

            $feedback_component = new FeedbackComponent(
                new ScoringComponent($q, $answer), 
                new AnswerFeedbackComponent($q, $answer)
            );


            if(method_exists($answer, 'getSelectedIds'))
            {   
                $selected = $answer->getSelectedIds();
                $options = $q->getAnswerOptions()->getOptions();
                foreach ($options as $idx => $opt) {
                   if(in_array($opt->getOptionId(), $selected)) {
                        $opt_htm .= $opt->getDisplayDefinition()->getText();
                    }
                }
            }

            $template->setCurrentBlock('instant_feedback');
            $template->setVariable('FB_OPTIONS', $opt_htm);
            $template->setVariable('INSTANT_FEEDBACK', $feedback_component->getHtml());
            $template->parseCurrentBlock();

         
        }
        
        $template->setVariable("TITLE", $this->object->getTitle());
        $template->setVariable("DESCRIPTION", $this->object->getDescription());
        $this->tpl->setContent($template->get());
    }
}


