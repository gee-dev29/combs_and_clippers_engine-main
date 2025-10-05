<?php

namespace App\Exceptions;

use Exception;
use Throwable;
use Illuminate\Support\Facades\Log as Logger;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
        Logger::info('Exception log', [$request->fullUrl() . ' - ' . $exception->getMessage()]);
        // This will replace our 404 response with a JSON response. && $request->wantsJson()
        if ($exception instanceof ModelNotFoundException) {
            return response()->json([
                'error' => 'Resource item not found.'
            ], 404);
        }

        if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                "ResponseStatus" => "Unsuccessful", "ResponseCode" => 405,
                "ResponseMessage" => 'Your request Method or URI is not found.'
            ], 405);
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            return response()->json([
                "ResponseStatus" => "Unsuccessful", "ResponseCode" => 405,
                "ResponseMessage" => 'Your request Method or URI is not allowed.'
            ], 405);
        }

        if ($exception instanceof ValidationException) {
            $errors = $exception->errors();
            // Get the first field (in this case "email")
            $firstField = array_key_first($errors);

            // Get the first error message for that field
            $firstErrorMessage = $errors[$firstField][0] ?? 'Validation failed';

            return response()->json(["ResponseCode" => 422, 
            "ResponseStatus" => "Unsuccessful", 
            "ResponseMessage" => $firstErrorMessage, 
            'Detail' => $exception->errors()], 422);

        }

        if ($exception instanceof AuthorizationException) {
            if($request->isJson()){


                return response()->json(["ResponseCode" => 403, 
                "ResponseStatus" => "Unsuccessful", 
                "ResponseMessage" => 'You don\'t have permission for this action ',
                'Detail' => 'UNAUTHORIZED_ACTION'], 403);
            }



        }

        return parent::render($request, $exception);
    }
}
