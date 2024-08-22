<?php

namespace weebz\yii2basics\controllers\rest;

use Yii;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use yii\imagine\Image;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Format\Video\X264;
use weebz\yii2basics\models\File;

class StorageController extends ControllerRest {
    
    public function actionGetFile(){
        try {
            if ($this->request->isPost) {

                $post = $this->request->post();
                $file_name = $post['file_name'] ?? false;
                $description = $post['description'] ?? false;
                $id = $post['id'] ?? false;
                $file = null;
                if($file_name) {
                    $file = File::find()->where(['name'=>$file_name])->all();
                    return $file;
                } else if($description) {
                    $file = File::find()->where(['like','description',$description])->all();
                    return $file;
                } else if($id) {
                    $file = File::find()->where(['id'=> $id])->one();
                    return $file;
                }
            }
            throw new \yii\web\BadRequestHttpException(Yii::t('app', 'Bad Request.'));
        } catch (\Throwable $th) {
            throw new \yii\web\ServerErrorHttpException(Yii::t('app', 'Server Error.'));
        }
    }

    public function actionSend()
    {
        
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        try {

            $webroot = Yii::getAlias('@webroot');
            $upload_folder = Yii::$app->params['upload.folder'];
            $web = Yii::getAlias('@web');

            $files_folder = "/{$upload_folder}";
            $upload_root = "{$webroot}{$files_folder}";
            $webFiles = "{$web}{$files_folder}";

            $group_id = null;
            $folder_id = null;
            $duration = 0;
            $save  = 0;
            $name = '';
            $description = '';
            $filePath = '';
            $filePathThumb = '';
            $fileUrl = '';
            $fileThumbUrl = '';
            $ext = '';
            $type = '';

            $model = new File();

            if ($this->request->isPost && ($temp_file = UploadedFile::getInstanceByName('file')) !== null) {

                $post = $this->request->post();

                $file_name = $post['file_name'] ?? false;
                $description = $post['description'] ?? $temp_file->name;
                $folder_id = $post['folder_id'] ?? 1;
                $save = $post['save'] ?? 0;
                $convert_video = $post['convert_video'] ?? false;
                $convert_video_format = $post['convert_video_format'] ?? 'mp4';
                
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

                    if($folder_id === 1){
                        $folder_id = 2;
                    }

                    $path = "{$files_folder}/images";
                    $pathThumb = "{$files_folder}/images/thumbs";
                    $pathRoot = "{$upload_root}/images";
                    $pathThumbRoot = "{$upload_root}/images/thumbs";

                    $filePath = "{$path}/{$name}";
                    $filePathThumb = "{$pathThumb}/{$name}";
                    $filePathRoot = "{$pathRoot}/{$name}";
                    $filePathThumbRoot = "{$pathThumbRoot}/{$name}";

                    $fileUrl = "{$webFiles}/images/{$name}";
                    $fileThumbUrl = "{$webFiles}/images/{$name}";

                    if (!file_exists($pathRoot)) {
                        FileHelper::createDirectory($pathRoot);
                    }

                    if (!file_exists($pathThumbRoot)) {
                        FileHelper::createDirectory($pathThumbRoot);
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
                    
                    $errors[] = $temp_file->saveAs($filePathRoot, ['quality' => 90]);
                    $errors[] = Image::crop($filePathRoot, $min, $min, $point)
                    ->save($filePathThumbRoot, ['quality' => 100]);
                    
                    if($min > 300){
                        $errors[] = Image::thumbnail($filePathThumbRoot, 300, 300)
                        ->save($filePathThumbRoot, ['quality' => 100]);
                    }

                } else if ($type == 'video') {

                    if($folder_id === 1){
                        $folder_id = 3;
                    }

                    if (!empty($file_name)) {
                        $name = "{$file_name}.mp4";
                    } else {
                        $name = 'file_' . date('dmYhims') . \Yii::$app->security->generateRandomString(6) . ".mp4";
                    }

                    $fileTemp = "{$upload_root}/{$temp_file->name}";

                    $path = "{$files_folder}/videos";
                    $pathRoot = "{$upload_root}/videos";
                    $filePath = "{$path}/{$name}";
                    $filePathRoot = "{$pathRoot}/{$name}";

                    $fileUrl = "{$web}/videos/{$name}";

                    if (!file_exists($pathRoot)) {
                        FileHelper::createDirectory($pathRoot);
                    }

                    if ($ext != 'mp4') {
                        $errors[] = $temp_file->saveAs($fileTemp, ['quality' => 90]);
                        $ffmpeg = FFMpeg::create();
                        $video = $ffmpeg->open($fileTemp);
                        $video->save(new X264(), $filePathRoot);
                        unlink($fileTemp);
                        $ext = 'mp4';
                    } else {
                        $errors[] = $temp_file->saveAs($filePathRoot, ['quality' => 90]);
                    }

                    $sec = 2;
                    $video_thumb_name = str_replace('.', '_', $name) . '.jpg';
                    $pathThumb = "{$files_folder}/videos/thumbs";
                    $pathThumbRoot = "{$upload_root}/videos/thumbs";
                    $filePathThumb = "{$pathThumb}/{$video_thumb_name}";
                    $filePathThumbRoot = "{$pathThumbRoot}/{$video_thumb_name}";
                    $fileThumbUrl = "{$web}/videos/{$name}";

                    if (!file_exists($pathThumbRoot)) {
                        FileHelper::createDirectory($pathThumbRoot);
                    }
                    
                    $ffmpeg = FFMpeg::create();
                    $video = $ffmpeg->open($filePathRoot);
                    $frame = $video->frame(TimeCode::fromSeconds($sec));
                    $frame->save($filePathThumbRoot);

                    $image_size = getimagesize($filePathThumbRoot);
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

                    $errors[] = Image::crop($filePathThumbRoot, $min, $min, $point)
                    ->save($filePathThumbRoot, ['quality' => 100]);

                    if($min > 300){
                        $errors[] = Image::thumbnail($filePathThumbRoot, 300, 300)
                        ->save($filePathThumbRoot, ['quality' => 100]);
                    }

                    $ffprobe = FFProbe::create();
                    $duration = $ffprobe
                        ->format($filePathRoot) // extracts file informations
                        ->get('duration');
                        
                } else {

                    if($folder_id === 1){
                        $folder_id = 4;
                    }

                    $path = "{$files_folder}/docs";
                    $pathRoot = "{$upload_root}/docs";
                    $filePath = "{$path}/{$name}";
                    $filePathRoot = "{$pathRoot}/{$name}";
                    $fileUrl = "{$webFiles}/images/{$name}";

                    if (!file_exists($pathRoot)) {
                        FileHelper::createDirectory($pathRoot);
                    }
                    
                    $errors[] = $temp_file->saveAs($filePathRoot, ['quality' => 90]);
                }

                $file_uploaded = [
                    'group_id' => $group_id,
                    'folder_id' => $folder_id,
                    'name' => $name,
                    'description' => $description,
                    'path' => $filePath,
                    'url' => $fileUrl,
                    'pathThumb' => $filePathThumb,
                    'urlThumb' => $fileThumbUrl,
                    'extension' => $ext,
                    'type' => $type,
                    'size' => $temp_file->size,
                    'duration' => intval($duration),
                    'created_at' => date('Y-m-d h:i:s')
                ];

                if($save){

                    if($group_id === null){
                        $user_groups = AuthController::getUserByToken()->getUserGroupsId();
                        $file_uploaded['group_id'] = end($user_groups);
                    }
                    
                    $file_uploaded['class'] = File::class;
                    $file_uploaded['file'] = $temp_file;
                    $model = Yii::createObject($file_uploaded);

                    if($model->save()){
                        return $model;
                    }else{
                        return $model->getErrors();
                    }
                    
                }

                return $file_uploaded;

            }
            
            throw new \yii\web\BadRequestHttpException(Yii::t('app', 'Bad Request.'));
        } catch (\Throwable $th) {
            throw new \yii\web\ServerErrorHttpException(Yii::t('app', 'Server Error.'));
        }
    }

}

?>