<?php
/**
 * Малки помощници за админ екраните (КДБФ, актове, етикети):
 * CSRF токен, скрит iframe за изпращане, презареждане на съдържанието.
 */

namespace SLiMS\Bg;

use simbio_form_maker;

class Ui
{
    public static function h($v): string
    {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }

    public static function date(?string $d): string
    {
        if (!$d || substr($d, 0, 10) === '0000-00-00') return '';
        return date('d.m.Y', strtotime($d));
    }

    /** Скритите полета с CSRF токена (същия механизъм като формите на SLiMS). */
    public static function token(string $formName): string
    {
        $token = simbio_form_maker::genRandomToken();
        simbio_form_maker::addTokenToPool($formName, $token);
        return '<input type="hidden" name="csrf_token" value="' . self::h($token) . '" />'
            . '<input type="hidden" name="form_name" value="' . self::h($formName) . '" />';
    }

    public static function tokenValid(): bool
    {
        return simbio_form_maker::isTokenValid();
    }

    /** Iframe-ът, в който се изпращат формите (gui.js показва индикатора за зареждане). */
    public static function submitFrame(): string
    {
        return '<iframe name="submitExec" id="submitExec" class="noBlock" style="display: none; visibility: hidden; width: 100%; height: 0;"></iframe>';
    }

    /** Презарежда основното съдържание на админ панела (от iframe-а за изпращане). */
    public static function reload(string $url): void
    {
        // изпълнява се в основния прозорец след малко: презареждането премахва този iframe
        // и скрипт от премахнат iframe спира по средата
        $call = "jQuery('#mainContent').simbioAJAX(" . json_encode($url) . ");";
        echo '<script type="text/javascript">parent.setTimeout(' . json_encode($call) . ', 50);</script>';
    }

    public static function toast(string $title, string $message, string $type = 'info'): void
    {
        \utility::jsToastr($title, $message, $type);
    }

    /** Падащ списък. */
    public static function select(string $name, array $options, $selected = '', string $attr = 'class="form-control"'): string
    {
        $o = '<select name="' . self::h($name) . '" id="' . self::h($name) . '" ' . $attr . '>';
        foreach ($options as $value => $label) {
            $o .= '<option value="' . self::h($value) . '"' . ((string)$value === (string)$selected ? ' selected' : '') . '>' . self::h($label) . '</option>';
        }
        return $o . '</select>';
    }

    /** Ред от формуляр: етикет + поле (+ помощен текст). */
    public static function row(string $label, string $control, string $help = ''): string
    {
        return '<div class="form-group row mb-2"><label class="col-sm-3 col-form-label font-weight-bold">' . $label . '</label>'
            . '<div class="col-sm-9">' . $control . ($help !== '' ? '<small class="form-text text-muted">' . $help . '</small>' : '') . '</div></div>';
    }

    /** Заглавната кутия на страницата (като в останалите модули). */
    public static function header(string $title, array $buttons = [], string $extra = ''): string
    {
        $b = '';
        foreach ($buttons as $label => $url) {
            $b .= '<a href="' . self::h($url) . '" class="btn btn-default">' . self::h($label) . '</a>';
        }
        return '<div class="menuBox"><div class="menuBoxInner itemIcon"><div class="per_title"><h2>' . self::h($title) . '</h2></div>'
            . '<div class="sub_section">' . ($b ? '<div class="btn-group">' . $b . '</div>' : '') . $extra . '</div></div></div>';
    }

    /** Права: модул „Библиография“ + отделно право за менюто (освен за администратора). */
    public static function requireAccess(string $menuId, bool $write = false): void
    {
        $ok = \utility::havePrivilege('bibliography', 'r') && (!$write || \utility::havePrivilege('bibliography', 'w'));
        if ($ok && (int)$_SESSION['uid'] !== 1) $ok = \utility::haveAccess($menuId);
        if (!$ok) {
            die('<div class="errorBox">' . __('You are not authorized to view this section') . '</div>');
        }
    }

    /** Право за утвърждаване на актове, приключване на година и настройки. */
    public static function canApprove(): bool
    {
        return (int)$_SESSION['uid'] === 1 || \utility::haveAccess('bibliography.kdbf-approve');
    }
}
