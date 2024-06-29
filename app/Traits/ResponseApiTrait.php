<?php
/* =================================== START CATATAN PENTING TRAITS API =========================================

    * Send any success response
    * @param $params
    public function success($params)
        > Ini Digunakan untuk post params langsung array atau JSON
        > Mirip metode destructure dari react, jadi params tidak akan salah nama

=================================== END CATATAN PENTING TRAITS API =========================================== */

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use DateTime;
use DateTimeZone;

trait ResponseApiTrait
{
    // Set Response Default Message Data
    protected $msgSuccesDefault = 'Request Berhasil.';
    protected $msgFailedDefault = 'Request Tidak Valid, Silahkan hubungi Administrator.';
    protected $msgSuccessList   = 'Data Berhasil Ditampilkan.';
    protected $msgFailedList    = 'Data Tidak Ditemukan.';
    protected $msgSuccessCreate = 'Data Berhasil Ditambahkan.';
    protected $msgFailedCreate  = 'Data Gagal Ditambahkan.';
    protected $msgSuccessUpdate = 'Data Berhasil Diubah.';
    protected $msgFailedUpdate  = 'Data Gagal Diubah.';
    protected $msgSuccessDelete = 'Data Berhasil Dihapus.';
    protected $msgFailedDelete  = 'Data Gagal Dihapus.';

    // Set Response Default Message Login
    protected $msgLoginFailedUser = "User Anda belum terdaftar di system, mohon hubungi pihak admin.";
    protected $msgLoginFailedPassword = 'Username atau Password yang anda masukkan salah.';

    // Set Validation default message
    protected $msgValidationFailed = "Request Tidak Valid, Mohon isi data yang dibutuhkan.";

    // Set Error message Enviorenment
    protected $msgErrorEnv  = 'Enviorenment Tidak Valid, Mohon cek file .env ';

    protected function handleMessage($codeMessage) {
        switch ($codeMessage) {
            case 'defaultTrue':
                return $this->msgSuccesDefault;
                break;
            case 'defaultFalse':
                return $this->msgFailedDefault;
                break;
            case 'listTrue':
                return $this->msgSuccessList;
                break;
            case 'listFalse':
                return $this->msgFailedList;
                break;
            case 'createTrue':
                return $this->msgSuccessCreate;
                break;
            case 'createFalse':
                return $this->msgSuccessCreate;
                break;
            case 'updateTrue':
                return $this->msgSuccessUpdate;
                break;
            case 'updateFalse':
                return $this->msgFailedUpdate;
                break;
            case 'deleteTrue':
                return $this->msgSuccessDelete;
                break;
            case 'deleteFalse':
                return $this->msgFailedDelete;
                break;
            case 'loginFalseUser':
                return $this->msgLoginFailedUser;
                break;
            case 'loginFalseKaryawan':
                return $this->msgLoginFailedKaryawan;
                break;
            case 'loginFalsePassword':
                return $this->msgLoginFailedPassword;
                break;
            case 'validationFalse':
                return $this->msgValidationFailed;
                break;
            case 'errEnv':
                return $this->msgErrorEnv;
                break;
            default:
                return $this->msgFailedDefault;
                break;
        }
    }

    // Start function Pagination LIST, SEARCH > Banyak Data
    public function generateResponsePaging($totalData = 0)
	{
        // Start Konfigurasi paging & Menghindari error zero division
        $start = request()->input('start');
        $limit = request()->input('limit');

        // Start Konfigurasi paging & Menghindari error zero division
        if ($limit != 0) {
            $page = $start / $limit + 1;
            $total_pages = ceil($totalData / $limit);
        } else {
            // Handle the case where $limit is zero (e.g., set $page and $total_pages to default values)
            $page = 1; // Default page number
            $total_pages = 1; // Default total pages
        }
        // End Konfigurasi paging & Menghindari error zero division

        $response = [
            'page'             => $page,
            'total_pages'      => $total_pages,
            'records_per_page' => $limit,
            'total_records'    => $totalData
        ];

		return $response;
	}
    // End function Pagination LIST, SEARCH > Banyak Data

    // Start function LIST, SEARCH > Multiple Data
	public function showSuccessList($params = [])
	{
        $message  = $this->handleMessage($params['codeMessage'] ?? 'listTrue');
        $response = [
            'status' => [
                'code'    => 200,
                'message' => $message
            ],
            'data' => $params['data'] ?? null
        ];

        if ($params['isPaging']) {
            // Wajib $data['total'] dikirim dari m_
            $response['paging'] = $this->generateResponsePaging($params['totalData']);
        }

        $response['request'] = request()->input();

		return response($response, 200)->header('Content-Type', 'application/json');
	}
    // End function LIST, SEARCH > Multiple Data

    // Start function Single Data
	public function showSuccess($params)
	{
        $message  = $this->handleMessage($params['codeMessage'] ?? 'defaultTrue');
        $response = [
            'status' => [
                'code'    => 200,
                'message' => $message
            ],
            'data' => $params['data'] ?? null,
            'params' => request(),
            'request' => request()->input()
        ];

		return response($response, 200)
                ->header('Content-Type', 'application/json');
	}
    // End function Single Data

    // Start function Data Tidak Ditemukan
	public function showNotFound($params = [])
	{
        $message  = $this->handleMessage($params['codeMessage'] ?? 'listFalse');
        $response = [
            'status' => [
                'code'    => 404,
                'message' => $message
            ],
            'data' => $params['data'] ?? null,
            'request' => request()->input()

        ];

		return response($response, 404)->header('Content-Type', 'application/json');
	}
    // End function Data Tidak Ditemukan

    // Start function Validation Response
	public function showValidationResponse($params = [])
	{
        $message  = $this->handleMessage($params['codeMessage'] ?? 'validationFalse');
        $response = [
            'status' => [
                'code'    => 422,
                'message' => $message
            ],
            'data' => $params['data'] ?? null,
            'error' => $params['error'],
            'request' => request()->input()
        ];

		return response($response, 422)->header('Content-Type', 'application/json');
	}
    // End function Validation Response

    // Start function Bad Response
	public function showBadResponse($params = [])
	{
        $message  = $this->handleMessage($params['codeMessage'] ?? 'defaultFalse');
        $response = [
            'status' => [
                'code'    => 500,
                'message' => $message
            ],
            'data' => $params['data'] ?? null,
            'error' => $params['error'] ?? null,
            'request' => request()->input()
        ];

		return response($response, 500)->header('Content-Type', 'application/json');
	}

    // Start function Error Enviorenment
	public function showErrEnvResponse($params)
	{
        $message  = $this->handleMessage($params['codeMessage'] ?? 'errEnv');

        $response = [
            'status' => [
                'code'    => 508,
                'message' => $message
            ],
            'data' => $params['data'] ?? null,
            'error' => $params['error'] ?? null,
            'request' => request()->input()
        ];

		return response($response, 508)
                ->header('Content-Type', 'application/json');
	}

    // Start function Untuk LoginDLL
    public static function showErrorHeader($params = [])
    {
        $response = [
            'status' => [
                'code'    => 401,
                'message' => 'API Belum Terautentikasi, Silahkan masukkan header Accept: application/json.'
            ],
            'data' => $params['data'] ?? null,
            'request' => request()->input()

        ];

        return response($response, 401)
                ->header('Content-Type', 'application/json');
    }
    // End function Untuk LoginDLL

    public static function showMethodNotAllowed($allowedMethods)
    {
        $message = "Method: " . request()->method() . " tidak diizinkan. Harap gunakan metode berikut: {$allowedMethods}.";

        $response = [
            'status' => [
                'code'    => 405,
                'message' => $message
            ],
            'request' => [
                'method' => request()->method(),
                'uri' => request()->getRequestUri(),
                'allowed_methods' => $allowedMethods,
                'body' => request()->input()
            ]
        ];

        return response()->json($response, 405);
    }

}
