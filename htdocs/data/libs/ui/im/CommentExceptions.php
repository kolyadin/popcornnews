<?php
/**
 * User: anubis
 * Date: 05.04.13 17:20
 */

class NotImplementedException extends Exception {

    public function __construct($method) {
        parent::__construct($method.' is not implemented');
    }

}

class VoteForOwnedCommentException extends Exception {}

class AlreadyVotedException extends Exception {}

class AbuseForOwnedCommentException extends Exception {}

class AlreadyAbusedException extends Exception {}

class WrongRoomException extends Exception {}

class WrongMessageException extends Exception {}

class WrongMessageOwnerException extends Exception {}

class WrongVoteException extends Exception {}

class EmptyContentException extends Exception {}

class SpamCommentException extends Exception {}

class CommentDataBaseException extends Exception {

    public function __construct($message = "") {
        parent::__construct($message);
    }

}