<?php

abstract class cashAbstractWidget extends waWidget
{
    /**
     * @var waWidgetSettingsModel
     */
    private static $settingsModel;

    protected static function getSettingModel(): waWidgetSettingsModel
    {
        if (self::$settingsModel === null) {
            self::$settingsModel = new waWidgetSettingsModel();
        }

        return self::$settingsModel;
    }

    protected static function renderTemplate($template, $assign = []): string
    {
        if (!file_exists($template)) {
            return '';
        }
        $assign['ui'] = wa()->whichUI(wa()->getConfig()->getApplication());
        $assign['webasyst_ui'] = wa()->whichUI('webasyst');

        $view = wa()->getView();
        $old_vars = $view->getVars();
        $view->clearAllAssign();
        $view->assign($assign);
        $html = $view->fetch($template);
        $view->clearAllAssign();
        $view->assign($old_vars);

        return $html;
    }

    protected function incognitoUser()
    {
        $user = wa()->getUser();
        $this->incognitoMode = !$user || !$user->isAuth();
        if ($this->incognitoMode) {
            $appAdmins = (new waContactRightsModel())->getUsers('cash');
            wa()->getAuth()->auth(['id' => array_shift($appAdmins)]);
        }
    }

    protected function incognitoLogout(): void
    {
        if ($this->incognitoMode) {
            wa()->getAuth()->clearAuth();
        }
    }
}
