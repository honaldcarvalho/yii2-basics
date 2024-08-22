<?php

namespace weebz\yii2basics\controllers\rest;

use Yii;
use weebz\yii2basics\models\File;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use yii\imagine\Image;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Format\Video\X264;

class UploadController extends ControllerRest {

    public function actionSend()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $webroot = Yii::getAlias('@webroot');
        $web = Yii::getAlias('@web/files');
        $files_folder = "{$webroot}/files";
        $duration = 0;

        $model = new File();

        if ($this->request->isPost) {

            $post = $this->request->post();

            $file_name = $post['file_name'] ?? false;
            $description = $post['description'] ?? false;
            $folder_id = $post['folder_id'] ?? null;
            $convert_video = $post['convert_video'] ?? false;
            $convert_video_format = $post['convert_video_format'] ?? 'mp4';
            
            $temp_file = UploadedFile::getInstanceByName('file');

            //dd([$temp_file,$post]);

            $ext = $temp_file->extension;

            if (!empty($file_name)) {
                $name = "{$file_name}.{$ext}";
            } else {
                $name = 'file_' . date('dmYhims') . \Yii::$app->security->generateRandomString(6) . ".{$ext}";
            }

            $type = 'unknow';
            [$type,$format] = explode('/',$temp_file->type);

            if ($type == 'image') {

                $path = "{$files_folder}/images";
                $pathThumb = "{$files_folder}/images/thumbs";
                $filePath = "{$path}/{$name}";
                $filePathThumb = "{$pathThumb}/{$name}";
                $fileUrl = "{$web}/images/{$name}";
                $fileThumbUrl = "{$web}/images/{$name}";

                if (!file_exists($path)) {
                    FileHelper::createDirectory($path);
                }

                if (!file_exists($pathThumb)) {
                    FileHelper::createDirectory($pathThumb);
                }

                $image_size = getimagesize($temp_file->tempName);
                $major = $image_size[0]; //width
                $min = $image_size[1]; //height
                $mov = ($major - $min) / 2;
                $point = [$mov, 0];

                if ($major < $min) {
                    $major = $image_size[1];
                    $min = $image_size[0];
                    $mov = ($major - $min) / 2;
                    $point = [0, $mov];
                }

                $errors[] = $temp_file->saveAs($filePath, ['quality' => 90]);
                $errors[] = Image::crop($filePath, $min, $min, $point)
                ->save($filePathThumb, ['quality' => 100]);

                if($min > 300){
                    $errors[] = Image::thumbnail($filePathThumb, 300, 300)
                    ->save($filePathThumb, ['quality' => 100]);
                }

            } else if ($type == 'video') {

                $path = "{$files_folder}/videos";
                $filePath = "{$path}/{$name}";
                $fileTemp = "{$path}/{$temp_file->name}";

                $fileUrl = "{$web}/videos/{$name}";

                if (!file_exists($path)) {
                    FileHelper::createDirectory($path);
                }

                if (!file_exists($pathThumb)) {
                    FileHelper::createDirectory($pathThumb);
                }

                if ($ext != 'mp4') {
                    $errors[] = $temp_file->saveAs($fileTemp, ['quality' => 90]);
                    $ffmpeg = FFMpeg::create();
                    $video = $ffmpeg->open($fileTemp);
                    $video->save(new X264(), $filePath);
                    unlink($fileTemp);
                } else {
                    $errors[] = $temp_file->saveAs($filePath, ['quality' => 90]);
                }

                $sec = 2;
                $video_thumb_name = str_replace('.', '_', $name) . '.jpg';
                $pathThumb = "{$files_folder}/videos/thumbs";
                $fileThumbUrl = "{$web}/videos/{$name}";
                $filePathThumb = "{$pathThumb}/{$video_thumb_name}";

                $ffmpeg = FFMpeg::create();
                $video = $ffmpeg->open($filePath);
                $frame = $video->frame(TimeCode::fromSeconds($sec));
                $frame->save($filePathThumb);

                $image_size = getimagesize($filePathThumb);
                $major = $image_size[0]; //width
                $min = $image_size[1]; //height
                $mov = ($major - $min) / 2;
                $point = [$mov, 0];

                if ($major < $min) {
                    $major = $image_size[1];
                    $min = $image_size[0];
                    $mov = ($major - $min) / 2;
                    $point = [0, $mov];
                }

                $errors[] = Image::crop($filePathThumb, $min, $min, $point)
                ->save($filePathThumb, ['quality' => 100]);

                if($min > 300){
                    $errors[] = Image::thumbnail($filePathThumb, 300, 300)
                    ->save($filePathThumb, ['quality' => 100]);
                }

            } else {

            }

            return [
                'name' => $name,
                'description' => $description,
                'path' => $filePath,
                'pathThumb' => $filePathThumb,
                'url' => $fileUrl,
                'urlThumb' => $fileThumbUrl,
                'extension' => $ext,
                'type' => $type,
                'duration' => intval($duration),
                'size' => $temp_file->size,
                'created_at' => \Yii::$app->formatter->asDate(date('Y-m-d h:i:s'))
            ];

        }
    }
}

?>