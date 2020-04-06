<?php declare(strict_types=1);

use srag\asq\AsqGateway;
use srag\asq\Application\Service\AuthoringContextContainer;


/**
 * @ilCtrl_Calls ilAQSEditQuestionsGUI: AsqQuestionAuthoringGUI
 */
class ilAQSEditQuestionsGUI
{
	const CMD_EDIT_QUESTIONS = \ilObjAQSTestGUI::TAB_EDITQUESTIONS;


	public function __construct(\ilObjAQSTest $object) {
		global $DIC;
		$this->ctrl = $DIC->ctrl();
		$this->tpl = $DIC->ui()->mainTemplate();
		$this->access = $DIC->access();
		$this->app_event_handler = $DIC["ilAppEventHandler"];
		
		$this->object = $object;
	}

    public function executeCommand() {
        global $DIC;

        $next_class = $DIC->ctrl()->getNextClass($this);
        switch (strtolower($next_class)) {
            case strtolower(AsqQuestionAuthoringGUI::class):
                    $this->forwardCommandToAuthoringGui();
                    break;
            default:
                switch ($cmd) {
                    default:
                		$this->renderToolbar();
                        $this->showQuestionsOverview();
                }
 
        }
        return true;

    }

	public function renderToolbar()
    {
        global $DIC;

        $link = AsqGateway::get()->link()->getCreationLink();
        $button = ilLinkButton::getInstance();
        $button->setUrl($link->getAction());
        $button->setCaption($link->getLabel(), false);
        $DIC->toolbar()->addButtonInstance($button);
    }

    protected function forwardCommandToAuthoringGui()
    {
        global $DIC;

        $backLink = $DIC->ui()->factory()->link()->standard(
            $DIC->language()->txt('back'),
            $DIC->ctrl()->getLinkTarget($this, self::CMD_EDIT_QUESTIONS));

        $authoring_context_container = new AuthoringContextContainer(
            $backLink,
            (int)$this->object->getRefId(),
            (int)$this->object->getId(),
            $this->object->getType(),
            (int)$DIC->user()->getId()
        );

        $asq = new AsqQuestionAuthoringGUI($authoring_context_container);
        $DIC->ctrl()->forwardCommand($asq);
    }


    protected function showQuestionsOverview() {
		global $DIC;
        $questions = AsqGateway::get()->question()->getQuestionsOfContainer($this->object->getId());
        $items = [];
        foreach ($questions as $question) {
        
        	if(! $question->getData()) {
        		continue;
        	}

            $items[] = $DIC->ui()->factory()->item()
                ->standard(
                    $question->getData()->getQuestionText()
                )
                ->withLeadText($question->getData()->getTitle())
                ->withActions(
                    $DIC->ui()->factory()->dropdown()->standard(
                        [
                            AsqGateway::get()->link()->getEditLink($question->getId()),
                            AsqGateway::get()->link()->getPreviewLink($question->getId()),
                            AsqGateway::get()->link()->getEditPageLink($question->getId()),
                            AsqGateway::get()->link()->getEditFeedbacksLink($question->getId())
                        ]
                    )
            );
        }
        $item_list = $DIC->ui()->factory()->listing()->unordered($items);

		$html = $DIC->ui()->renderer()->render($item_list);
        //$DIC->ui()->mainTemplate()->setContent($html);
		$this->tpl->setContent($html);
    }
}
