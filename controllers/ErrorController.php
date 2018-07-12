<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/10/17
 * Time: 下午3:42
 */
namespace app\controllers;

use yii\base\Exception;
use yii\base\UserException;
use app\helpers\Brower;
use Yii;
use yii\web\HttpException;

class ErrorController extends BaseController
{

    public function actionIndex()
    {

        if (($exception = Yii::$app->getErrorHandler()->exception) === null) {
            // action has been invoked not from error handler, but by direct route, so we display '404 Not Found'
            $exception = new HttpException(404, Yii::t('yii', 'Page not found.'));
        }

        if ($exception instanceof HttpException) {
            $code = $exception->statusCode;
        } else {
            $code = $exception->getCode();
        }
        if ($exception instanceof Exception) {
            $name = $exception->getName();
        } else {
            $name = $this->defaultName ?: Yii::t('yii', 'Error');
        }
        if ($code) {
            $name .= " (#$code)";
        }

        if ($exception instanceof UserException) {
            $message = $exception->getMessage();
        } else {
            $message = Yii::t('yii', 'An internal server error occurred.');
        }

        if (Yii::$app->getRequest()->getIsAjax()) {
            return "$name: $message";
        } else {
            $from = Brower::whereFrom();
            $view=($from==2)?'dd_index':'index';
            return $this->render($view , [
                'code' => $code,
                'name' => $name,
                'message' => $message,
                'exception' => $exception,
            ]);
        }
    }
}