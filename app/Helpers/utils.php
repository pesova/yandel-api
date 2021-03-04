<?php

use Intervention\Image\Facades\Image;

/**
 * Function for generating random strings
 * which could be alphabets, integer or a mixed
 *
 * @param int $stringLength - length of generated strings default return
 * @param bool $numDays - determines if method should returnn only numeric strings
 *
 * @return string
 */
if(!function_exists('random_strings'))
{
  function random_strings($stringLength = 12, $numeric = false): string
  {
    $seed = $numeric ? '0123456789'
            : '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    return substr( str_shuffle($seed), 0, $stringLength );
  }
}

if(!function_exists('format_money'))
{
  function format_money(string $money)
  {
    $money = str_replace(',', '', $money);
    return number_format((float)$money, 2, '.', '');
  }
}

if(!function_exists('saveImage'))
{
  function  saveImage($image, $name = null, $location = '')
  {
    if( ! is_string($image) ) $image = base64_encode( file_get_contents($image) );
    if( !str_ends_with($location, '/') ) $location.='/';

    $imageName = ($name ?? auth()->user()->username).'.png';
    $filePath = $location.$imageName;

    $image = str_replace('data:image/png;base64,', '', $image);
    $image = str_replace(' ', '+', $image);
    $image = base64_decode($image);
    Storage::disk('public')->put($filePath, $image);

    return $imageName;
  }
}

/**
 * resize an image and save it
 *
 * @param $avater
 * @return string
 * @throws \Intervention\Image\Exception\NotWritableException
 */
if (!function_exists('saveAndCompressImage'))
{
    function saveImage($imageFile, $filePath = 'company_logos')
    {
        $fileName = time() . '.' . $imageFile->getClientOriginalExtension();
        Image::make($imageFile)->resize(300, 300)
            ->save(storage_path("app/public/{$filePath}/{$fileName}"));

        return $fileName;
    }
}

/**
 * Universally handles throwable exceptions
 * by loggin them into specified channels
 *
 * @param Throwable $e
 * @param string $logChannel = 'app'
 * @param mixed $data = null
 *
 * @return void
 */
if(!function_exists('handleThrowable'))
{
  function handleThrowable(Throwable $e, string $logChannel = 'app', string $title = 'title', $data = null) 
  {
    \Log::channel($logChannel)->error([
      'message' => $e->getMessage().' in '.$e->getFile().' on line '.$e->getLine(),
      $title ? $title : 'data' => $data
    ]);
  }
}
