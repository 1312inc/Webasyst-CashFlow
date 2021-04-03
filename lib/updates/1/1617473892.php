<?php
try {
    (new cashModel())->exec("ALTER TABLE `cash_transaction` CHANGE `create_contact_id` `create_contact_id` INT(11) DEFAULT NULL");
} catch (waException $exception) {

}