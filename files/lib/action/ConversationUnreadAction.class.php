<?php 
namespace wcf\action;
use wcf\data\conversation\Conversation;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\request\LinkHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Allows the user to change a conversation to unread
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.conversation.unread
 */
class ConversationUnreadAction extends AbstractSecureAction {
	/**
	 * id of affected conversation
	 */
	public $conversationID = 0;
	
	/**
	 * @inheritDoc
	 */
	public $loginRequired = true;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_GET['id'])) $this->conversationID = intval($_GET['id']);
		$conversation = Conversation::getUserConversation($this->conversationID, WCF::getUser()->userID);
		if ($conversation === null || !$conversation->canRead()) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		$sql = "UPDATE	wcf".WCF_N."_conversation_to_user
				SET		lastVisitTime = ?
				WHERE	conversationID = ? AND participantID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([0, $this->conversationID, WCF::getUser()->userID]);
		
		UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'unreadConversationCount');
		
		$this->executed();
		
		HeaderUtil::redirect(LinkHandler::getInstance()->getLink('ConversationList'));
		exit;
	}
}
