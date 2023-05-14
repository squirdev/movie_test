<?php

namespace App\Library;

use App\Models\AppConfig;
use App\Models\EmailTemplates;
use App\Models\PaymentMethods;
use Carbon\Carbon;
use Exception;
use FilesystemIterator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Auth;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SimpleXMLElement;

/**
 * @method static returnBytes(string $ini_get)
 */
class Tool
{

    /**
     *  Get all time zone.
     *
     * @return array
     */
    public static function allTimeZones(): array
    {
        // Get all time zones with offset
        $zones_array = [];
        $timestamp   = time();
        foreach (timezone_identifiers_list() as $key => $zone) {
            date_default_timezone_set($zone);
            $zones_array[$key]['zone']  = $zone;
            $zones_array[$key]['text']  = '(GMT'.date('P', $timestamp).') '.$zones_array[$key]['zone'];
            $zones_array[$key]['order'] = str_replace('-', '1', str_replace('+', '2', date('P', $timestamp))).$zone;
        }

        // sort by offset
        usort($zones_array, function ($a, $b) {
            return strcmp($a['order'], $b['order']);
        });

        return $zones_array;
    }

    /**
     * Get options array for select box.
     *
     * @return array
     *
     */
    public static function getTimezoneSelectOptions(): array
    {
        $arr = [];
        foreach (self::allTimeZones() as $timezone) {
            $row   = ['value' => $timezone['zone'], 'text' => $timezone['text']];
            $arr[] = $row;
        }

        return $arr;
    }

    /**
     * Format display datetime.
     *
     * @param $datetime
     *
     * @return mixed
     */
    public static function formatDateTime($datetime)
    {
        return self::dateTime($datetime)->format(trans('locale.labels.datetime_format'));
    }

    /**
     * Format display datetime.
     *
     * @return mixed
     *
     */
    public static function dateTime($datetime)
    {
        $timezone = self::currentTimezone();
        $result   = $datetime;

        return $result->timezone($timezone);
    }

    /**
     * Format display datetime.
     *
     * @return mixed
     *
     */
    public static function customerDateTime($datetime)
    {
        $timezone = is_object(Auth::user()) ? Auth::user()->timezone : '';
        $result   = $datetime;
        if ( ! empty($timezone)) {
            $result = $result->timezone($timezone);
        }

        $format = config('app.date_format').', g:i A';

        return $result->format($format);
    }

    /**
     * Format display datetime.
     *
     * @return mixed
     *
     */
    public static function dateTimeFromString($time_string)
    {
        return self::dateTime(Carbon::parse($time_string));
    }


    /**
     * Human time format.
     *
     * @param $time
     *
     * @return mixed
     */

    public static function formatHumanTime($time)
    {
        return $time->diffForHumans();
    }

    /**
     * Change singular to plural.
     *
     * @param $phrase
     * @param $value
     *
     * @return string
     */
    public static function getPluralParse($phrase, $value): string
    {
        $plural = '';
        if ($value > 1) {
            for ($i = 0; $i < strlen($phrase); ++$i) {
                if ($i == strlen($phrase) - 1) {
                    $plural .= ($phrase[$i] == 'y' && $phrase != 'day') ? 'ies' : (($phrase[$i] == 's' || $phrase[$i] == 'x' || $phrase[$i] == 'z' || $phrase[$i] == 'ch' || $phrase[$i] == 'sh') ? $phrase[$i].'es' : $phrase[$i].'s');
                } else {
                    $plural .= $phrase[$i];
                }
            }

            return $plural;
        }

        return $phrase;
    }

    /**
     * Get file/folder permissions.
     *
     *
     * @param $path
     *
     * @return string
     */
    public static function getPerms($path): string
    {
        return substr(sprintf('%o', fileperms($path)), -4);
    }

    /**
     * Get system time conversion.
     *
     *
     *
     * @param $time
     *
     * @return Carbon
     */
    public static function systemTime($time): Carbon
    {
        return $time->setTimezone(config('app.timezone'));
    }

    /**
     * Get system time conversion.
     *
     *
     * @param $string
     * @param  null  $timezone
     *
     * @return Carbon
     */
    public static function systemTimeFromString($string, $timezone = null): Carbon
    {
        if ($timezone == null) {
            $timezone = self::currentTimezone();
        }

        $time = Carbon::createFromFormat('Y-m-d H:i', $string, $timezone);

        return self::systemTime($time);
    }


    /**
     * Get max upload file.
     *
     * @return string
     */
    public static function maxFileUploadInBytes(): string
    {
        //select maximum upload size
        $max_upload = self::returnBytes(ini_get('upload_max_filesize'));
        //select post limit
        $max_post = self::returnBytes(ini_get('post_max_size'));

        // return the smallest of them, this defines the real limit
        return min($max_upload, $max_post);
    }

    /**
     * Day of week select options.
     *
     *
     * @return array
     */
    public static function dayOfWeekSelectOptions(): array
    {
        return [
            ['value' => '1', 'text' => 'Monday'],
            ['value' => '2', 'text' => 'Tuesday'],
            ['value' => '3', 'text' => 'Wednesday'],
            ['value' => '4', 'text' => 'Thursday'],
            ['value' => '5', 'text' => 'Friday'],
            ['value' => '6', 'text' => 'Saturday'],
            ['value' => '7', 'text' => 'Sunday'],
        ];
    }

    /**
     * Day of week arrays.
     *
     *
     * @return array
     */
    public static function weekdaysArray(): array
    {
        $array = [];
        foreach (self::dayOfWeekSelectOptions() as $day) {
            $array[$day['value']] = $day['text'];
        }

        return $array;
    }

    /**
     * Month select options.
     *
     * @return array
     */
    public static function monthSelectOptions(): array
    {
        return [
            ['value' => '1', 'text' => 'January'],
            ['value' => '2', 'text' => 'February'],
            ['value' => '3', 'text' => 'March'],
            ['value' => '4', 'text' => 'April'],
            ['value' => '5', 'text' => 'May'],
            ['value' => '6', 'text' => 'June'],
            ['value' => '7', 'text' => 'July'],
            ['value' => '8', 'text' => 'August'],
            ['value' => '9', 'text' => 'September'],
            ['value' => '10', 'text' => 'October'],
            ['value' => '11', 'text' => 'November'],
            ['value' => '12', 'text' => 'December'],
        ];
    }

    /**
     * Month array.
     *
     * @return array
     */
    public static function monthsArray(): array
    {
        $array = [];
        foreach (self::monthSelectOptions() as $day) {
            $array[$day['value']] = $day['text'];
        }

        return $array;
    }

    /**
     * Week select options.
     *
     * @return array
     */
    public static function weekSelectOptions(): array
    {
        return [
            ['value' => '1', 'text' => '1st_week'],
            ['value' => '2', 'text' => '2nd_week'],
            ['value' => '3', 'text' => '3rd_week'],
            ['value' => '4', 'text' => '4th_week'],
            ['value' => '5', 'text' => '5th_week'],
        ];
    }

    /**
     * Week array.
     *
     * @return array
     */
    public static function weeksArray(): array
    {
        $array = [];
        foreach (self::weekSelectOptions() as $day) {
            $array[$day['value']] = $day['text'];
        }

        return $array;
    }

    /**
     * Month select options.
     *
     * @return array
     */
    public static function dayOfMonthSelectOptions(): array
    {
        $arr = [];
        for ($i = 1; $i < 32; ++$i) {
            $arr[] = ['value' => $i, 'text' => $i];
        }

        return $arr;
    }

    /**
     * Get day string from timestamp.
     *
     * @param $timestamp
     *
     * @return mixed
     */
    public static function dayStringFromTimestamp($timestamp)
    {
        if (isset($timestamp) && $timestamp != '0000-00-00 00:00:00') {
            // @todo: hard day format code: 'Y-m-d'
            $result = Tool::dateTime($timestamp)->format('Y-m-d');
        } else {
            $result = Tool::dateTime(Carbon::now())->format('Y-m-d');
        }

        return $result;
    }


    /**
     * Get time string from timestamp.
     *
     * @param $timestamp
     *
     * @return mixed
     */
    public static function timeStringFromTimestamp($timestamp)
    {
        if (isset($timestamp) && $timestamp != '0000-00-00 00:00:00') {
            // @todo: hard day format code: 'H:i'
            $result = Tool::dateTime($timestamp)->format('H:i');
        } else {
            $result = Tool::dateTime(Carbon::now())->format('H:i');
        }

        return $result;
    }

    /**
     * Convert numbers array to weekdays array.
     *
     * @param $numbers
     *
     * @return array
     */
    public static function numberArrayToWeekdaysArray($numbers): array
    {
        $weekdays_texts = self::weekdaysArray();
        $weekdays       = [];
        foreach ($numbers as $number) {
            $weekdays[] = $weekdays_texts[$number];
        }

        return $weekdays;
    }


    /**
     * Convert numbers array to weeks array.
     *
     * @param $numbers
     *
     * @return array
     */

    public static function numberArrayToWeeksArray($numbers): array
    {
        $weeks_texts = self::weeksArray();
        $weeks       = [];
        foreach ($numbers as $number) {
            $weeks[] = $weeks_texts[$number];
        }

        return $weeks;
    }

    /**
     * Convert numbers array to months array.
     *
     * @param $numbers
     *
     * @return array
     */
    public static function numberArrayToMonthsArray($numbers): array
    {
        $month_texts = self::monthsArray();
        $months      = [];
        foreach ($numbers as $number) {
            $months[] = $month_texts[$number];
        }

        return $months;
    }


    /**
     * Get day names from array of numbers.
     *
     * @param $numbers
     *
     * @return array
     */

    public static function getDayNamesFromArrayOfNumber($numbers): array
    {
        $names = [];

        $ends = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];
        foreach ($numbers as $number) {
            if (($number % 100) >= 11 && ($number % 100) <= 13) {
                $names[] = $number.'th';
            } else {
                $names[] = $number.$ends[$number % 10];
            }
        }

        return $names;
    }

    /**
     * Quota time unit options.
     *
     * @return array
     */
    public static function timeUnitOptions(): array
    {
        return [
            ['value' => 'minute', 'text' => __('locale.labels.minute')],
            ['value' => 'hour', 'text' => __('locale.labels.hour')],
            ['value' => 'day', 'text' => __('locale.labels.day')],
            ['value' => 'week', 'text' => __('locale.labels.week')],
            ['value' => 'month', 'text' => __('locale.labels.month')],
            ['value' => 'year', 'text' => __('locale.labels.year')],
        ];
    }

    /**
     * Get php paths select options.
     *
     * @param $paths
     *
     * @return array
     */
    public static function phpPathsSelectOptions($paths): array
    {
        $options = [];

        foreach ($paths as $path) {
            $options[] = [
                'text'  => $path,
                'value' => $path,
            ];
        }

        $options[] = [
            'text'  => 'php_bin_manual',
            'value' => 'manual',
        ];

        return $options;
    }

    /**
     *  Number select options.
     *
     * @param  int  $min
     * @param  int  $max
     *
     * @return array
     */
    public static function numberSelectOptions(int $min = 1, int $max = 100): array
    {
        $options = [];

        for ($i = $min; $i <= $max; ++$i) {
            $options[] = ['value' => $i, 'text' => $i];
        }

        return $options;
    }

    /**
     * Format price.
     *
     * @param        $price
     * @param  string  $format
     *
     * @return string
     */
    public static function format_price($price, string $format = '{PRICE}'): string
    {
        return str_replace('{PRICE}', self::format_number($price), $format);
    }

    /**
     * Format price.
     *
     * @param $number
     *
     * @return string
     */
    public static function format_number($number): string
    {
        if (is_numeric($number) && floor($number) != $number) {
            return number_format($number, 2, __('locale.labels.dec_point'), __('locale.labels.thousands_sep'));
        } elseif (is_numeric($number)) {
            return number_format($number, 0, __('locale.labels.dec_point'), __('locale.labels.thousands_sep'));
        } else {
            return $number;
        }
    }

    /**
     * Format display date.
     *
     * @param $datetime
     *
     * @return string
     */
    public static function formatTime($datetime): string
    {
        return ! isset($datetime) ? '' : self::dateTime($datetime)->format('h:i A');
    }

    /**
     * Format display date.
     *
     * @param $datetime
     *
     * @return string
     */
    public static function formatDate($datetime): string
    {
        return ! isset($datetime) ? '' : self::dateTime($datetime)->format('M d, Y');
    }


    /**
     * Get current timezone.
     *
     * @return string
     */
    public static function currentTimezone(): string
    {
        if (is_object(Auth::user())) {
            $timezone = is_object(Auth::user()) ? Auth::user()->timezone : '+00:00';
        } else {
            $timezone = '+00:00';
        }

        return $timezone;
    }

    /**
     *  Get Directory Size.
     *
     * @param $path
     *
     * @return int
     */
    public static function getDirectorySize($path): int
    {
        $bytestotal = 0;
        $path       = realpath($path);
        if ($path !== false) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object) {
                $bytestotal += $object->getSize();
            }
        }

        return $bytestotal;
    }


    /**
     * Get All File Types
     *
     * @param $filename
     *
     * @return string
     */

    public static function getFileType($filename): string
    {
        $mime_types = [

            'txt'  => 'text/plain',
            'htm'  => 'text/html',
            'html' => 'text/html',
            'php'  => 'text/html',
            'css'  => 'text/css',
            'js'   => 'application/javascript',
            'json' => 'application/json',
            'xml'  => 'application/xml',
            'swf'  => 'application/x-shockwave-flash',
            'flv'  => 'video/x-flv',

            // images
            'png'  => 'image/png',
            'jpe'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg'  => 'image/jpeg',
            'gif'  => 'image/gif',
            'bmp'  => 'image/bmp',
            'ico'  => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif'  => 'image/tiff',
            'svg'  => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip'  => 'application/zip',
            'rar'  => 'application/x-rar-compressed',
            'exe'  => 'application/x-msdownload',
            'msi'  => 'application/x-msdownload',
            'cab'  => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3'  => 'audio/mpeg',
            'qt'   => 'video/quicktime',
            'mov'  => 'video/quicktime',

            // adobe
            'pdf'  => 'application/pdf',
            'psd'  => 'image/vnd.adobe.photoshop',
            'ai'   => 'application/postscript',
            'eps'  => 'application/postscript',
            'ps'   => 'application/postscript',

            // ms office
            'doc'  => 'application/msword',
            'rtf'  => 'application/rtf',
            'xls'  => 'application/vnd.ms-excel',
            'ppt'  => 'application/vnd.ms-powerpoint',

            // open office
            'odt'  => 'application/vnd.oasis.opendocument.text',
            'ods'  => 'application/vnd.oasis.opendocument.spreadsheet',
        ];

        $arr = explode('.', $filename);
        $ext = strtolower(array_pop($arr));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        } elseif (function_exists('finfo_open')) {
            $finfo    = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);

            return $mimetype;
        } else {
            return 'application/octet-stream';
        }
    }


    /**
     * Check re-captcha success.
     *
     * @param $request
     *
     * @return bool
     * @throws GuzzleException
     */
    public static function checkReCaptcha($request): bool
    {
        if ( ! isset($request->all()['g-recaptcha-response'])) {
            return false;
        }

        // Check recaptcha
        $client = new Client();
        $res    = $client->post('https://www.google.com/recaptcha/api/siteverify', ['verify' => false, 'form_params' => [
            'secret'   => config('no-captcha.secret'),
            'remoteip' => $request->ip(),
            'response' => $request->all()['g-recaptcha-response'],
        ]]);

        return json_decode($res->getBody(), true)['success'];
    }

    /**
     * Format a number with delimiter.
     *
     * @param $number
     * @param  int  $precision
     * @param  string  $separator
     *
     * @return string
     */
    public static function number_with_delimiter($number, int $precision = 0, string $separator = ','): string
    {
        if ( ! is_numeric($number)) {
            return $number;
        }

        return number_format($number, $precision, '.', $separator);
    }


    /**
     * Reset max_execution_time so that command can run for a long time without being terminated.
     *
     * @return bool
     */
    public static function resetMaxExecutionTime(): bool
    {
        try {
            set_time_limit(0);
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '-1');

            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * get difference two multidimensional array
     *
     * @param $array1
     * @param $array2
     *
     * @return array
     */

    public static function check_diff_multi($array1, $array2): array
    {

        foreach (array_chunk($array1, 500) as $chunk) {
            foreach ($chunk as $key => $value) {
                if (in_array($value, $array2)) {
                    unset($array1[$key]);
                }
            }
        }

        return $array1;
    }

    public static function convert($size): string
    {
        $unit = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];

        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2).' '.$unit[$i];
    }


    /**
     * Upload and resize avatar.
     *
     * @param $message
     * @param $sending_server
     *
     * @return string
     * @throws Exception
     */
    public static function createVoiceFile($message, $sending_server): string
    {
        $path        = 'voice/';
        $upload_path = public_path($path);

        if ( ! file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }
        $get_file_path = null;
        $filename      = date('Ymdhis').'.xml';
        $file_path     = $upload_path.$filename;

        if ($sending_server == 'Twilio') {

            $string = '<Response>
                         <Say voice="alice">'.$message.'</Say>
                       </Response>';

            $get_voice_data = new SimpleXMLElement($string);
            file_put_contents($file_path, $get_voice_data->asXML());

            $get_file_path = asset('/voice').'/'.$filename;

        }
        if ($sending_server == 'Plivo') {

            $string = '<Response>
                         <Speak>'.$message.'</Speak>
                       </Response>';

            $get_voice_data = new SimpleXMLElement($string);
            file_put_contents($file_path, $get_voice_data->asXML());

            $get_file_path = asset('/voice').'/'.$filename;

        }

        return $get_file_path;

    }


    /**
     * Upload and resize avatar.
     *
     * @param $file
     *
     * @return string
     */
    public static function uploadImage($file): string
    {
        $path        = 'mms/';
        $upload_path = public_path($path);

        if ( ! file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        $filename = 'mms_'.time().'.'.$file->getClientOriginalExtension();

        // save to server
        $file->move($upload_path, $filename);

        return asset('/mms').'/'.$filename;

    }

    /**
     * Upload and resize avatar.
     *
     * @param $file
     *
     * @return string
     */
    public static function uploadFile($file): string
    {
        $path        = 'senderid_docs/';
        $upload_path = public_path($path);

        if ( ! file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        $filename = 'senderid_'.time().'.'.$file->getClientOriginalExtension();

        // save to server
        $file->move($upload_path, $filename);

        return asset('/senderid_docs').'/'.$filename;

    }

    public static function strReplaceFirst($search, $replace, $subject)
    {
        $search = '/'.preg_quote($search, '/').'/';

        return preg_replace($search, $replace, $subject, 1);
    }

    /**
     * validate number length
     *
     * @param $phone
     *
     * @return bool
     */
    public static function validatePhone($phone): bool
    {

        // Allow +, - and . in phone number
        $phone_to_check = filter_var($phone, FILTER_SANITIZE_NUMBER_INT);
        // Check the length of number
        // This can be customized if you want phone number from a specific country
        if (strlen($phone_to_check) < 7 || strlen($phone_to_check) > 15) {
            return false;
        }

        return true;
    }

    /**
     * generate GUID
     *
     * @return string
     */
    public static function GUID(): string
    {

        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }


    /**
     * render sms with tag
     *
     * @param $msg
     * @param $data
     *
     * @return string|string[]
     */
    public static function renderTemplate($msg, $data)
    {
        preg_match_all('~{(.*?)}~s', $msg, $datas);

        foreach ($datas[1] as $value) {
            if (array_key_exists($value, $data)) {
                $msg = preg_replace("/\b$value\b/u", $data[$value], $msg);
            } else {
                $msg = str_ireplace($value, '', $msg);
            }
        }

        return str_ireplace(["{", "}"], '', $msg);
    }

    /**
     * check is RTL or not
     *
     * @param $string
     *
     * @return false|int
     */
    public static function isRTL($string)
    {
        $rtl_chars_pattern = '/[\x{0590}-\x{05ff}\x{0600}-\x{06ff}]/u';

        return preg_match($rtl_chars_pattern, $string);
    }

    /**
     * @param $version
     *
     * @return bool|void
     */
    public static function versionSeeder($version)
    {
        switch ($version) {
            case '3.5.0':
                return false;

            case '3.4.0':

                $envSettings = '
TERMS_OF_USE='.'
PRIVACY_POLICY='.'
TRAI_DLT=false'.'
';

                // @ignoreCodingStandard
                $env        = file_get_contents(base_path('.env'));
                $rows       = explode("\n", $env);
                $unwanted   = "TERMS_OF_USE|PRIVACY_POLICY|TRAI_DLT";
                $cleanArray = preg_grep("/$unwanted/i", $rows, PREG_GREP_INVERT);

                $cleanString = implode("\n", $cleanArray);
                $env         = $cleanString.$envSettings;

                try {
                    file_put_contents(base_path('.env'), $env);

                    $categories = collect(config('customer-permissions'))->map(function ($value, $key) {
                        $value['name'] = $key;

                        return $value;
                    })->groupBy('default')->first->toArray();

                    $permissions = collect($categories)->map(function ($item) {
                        return $item['name'];
                    })->toArray();


                    $app_config = AppConfig::where('setting', 'customer_permissions')->first();

                    if ( ! $app_config) {
                        AppConfig::create([
                            'setting' => 'customer_permissions',
                            'value'   => json_encode($permissions),
                        ]);
                    }

                    $app_config = AppConfig::where('setting', 'login_notification_email')->first();
                    if ( ! $app_config) {
                        AppConfig::create([
                            'setting' => 'login_notification_email',
                            'value'   => false,
                        ]);
                    }

                    $email_template = EmailTemplates::where('slug', 'sender_id_confirmation')->first();

                    if ( ! $email_template) {
                        EmailTemplates::create(
                            [
                                'name'    => 'Sender ID Confirmation',
                                'slug'    => 'sender_id_confirmation',
                                'subject' => 'Sender ID Confirmation on {app_name}',
                                'content' => 'Hi,
                                      You sender id mark as: {status}. Login to your portal to show details.
                                      {sender_id_url}',
                                'status'  => true,
                            ]);
                    }

                    $payment_method = PaymentMethods::where('type', 'paygateglobal')->first();
                    if ( ! $payment_method) {
                        PaymentMethods::create(
                            [
                                'name'    => 'PaygateGlobal',
                                'type'    => 'paygateglobal',
                                'options' => json_encode([
                                    'api_key' => 'api_key',
                                ]),
                                'status'  => true,
                            ]);
                    }

                    return true;

                } catch (Exception $e) {
                    return false;
                }

            case '3.3.0':
            case '3.2.0':
            case '3.1.0':
            case '3.0.1':
            case '3.0.0':

                $categories = collect(config('customer-permissions'))->map(function ($value, $key) {
                    $value['name'] = $key;

                    return $value;
                })->groupBy('default')->first->toArray();

                $permissions = collect($categories)->map(function ($item) {
                    return $item['name'];
                })->toArray();


                $app_config = AppConfig::where('setting', 'customer_permissions')->first();

                if ( ! $app_config) {
                    AppConfig::create([
                        'setting' => 'customer_permissions',
                        'value'   => json_encode($permissions),
                    ]);
                }

                $app_config = AppConfig::where('setting', 'login_notification_email')->first();
                if ( ! $app_config) {
                    AppConfig::create([
                        'setting' => 'login_notification_email',
                        'value'   => false,
                    ]);
                }

                $email_template = EmailTemplates::where('slug', 'sender_id_confirmation')->first();

                if ( ! $email_template) {
                    EmailTemplates::create(
                        [
                            'name'    => 'Sender ID Confirmation',
                            'slug'    => 'sender_id_confirmation',
                            'subject' => 'Sender ID Confirmation on {app_name}',
                            'content' => 'Hi,
                                      You sender id mark as: {status}. Login to your portal to show details.
                                      {sender_id_url}',
                            'status'  => true,
                        ]);
                }

                $payment_method = PaymentMethods::where('type', 'paygateglobal')->first();
                if ( ! $payment_method) {
                    PaymentMethods::create(
                        [
                            'name'    => 'PaygateGlobal',
                            'type'    => 'paygateglobal',
                            'options' => json_encode([
                                'api_key' => 'api_key',
                            ]),
                            'status'  => true,
                        ]);
                }

                return true;
        }
    }
}
