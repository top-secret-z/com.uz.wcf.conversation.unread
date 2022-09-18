<?php

/*
 * Copyright by Udo Zaydowicz.
 * Modified by SoftCreatR.dev.
 *
 * License: http://opensource.org/licenses/lgpl-license.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
namespace wcf\action;

use wcf\data\conversation\Conversation;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\request\LinkHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Allows the user to change a conversation to unread
 */
class ConversationUnreadAction extends AbstractSecureAction
{
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
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_GET['id'])) {
            $this->conversationID = \intval($_GET['id']);
        }
        $conversation = Conversation::getUserConversation($this->conversationID, WCF::getUser()->userID);
        if ($conversation === null || !$conversation->canRead()) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        parent::execute();

        $sql = "UPDATE    wcf" . WCF_N . "_conversation_to_user
                SET        lastVisitTime = ?
                WHERE    conversationID = ? AND participantID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([0, $this->conversationID, WCF::getUser()->userID]);

        UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'unreadConversationCount');

        $this->executed();

        HeaderUtil::redirect(LinkHandler::getInstance()->getLink('ConversationList'));

        exit;
    }
}
