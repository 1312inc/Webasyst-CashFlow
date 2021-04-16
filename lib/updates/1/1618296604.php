<?php
try {
    (new cashModel())->exec("ALTER TABLE `cash_category` CHANGE `sort` `sort` INT(11) DEFAULT NULL");
    (new cashModel())->exec("ALTER TABLE `cash_account` CHANGE `sort` `sort` INT(11) DEFAULT NULL");
} catch (waException $exception) {

}