<?php

namespace weebz\yii2basics\models;

use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Format\Video\X264;
use Yii;
use yii\base\Model;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use yii\imagine\Image;

class UploadForm extends ModelCommon
{
    /**
     * @var UploadedFile
     */
    public $file;
    public $save_file_model = 1;
    public $file_name;
    public $folder_id;
    public $extensions = [];
    public $size;
    public $type;
    public $max_size = '2';
    public $convert_video = true;
    public $convert_video_format = 'mp4';
    public $preview;
    public $created_at;

    public function rules()
    {
        return [
            ['file_name','string'],
            ['file', 'safe'],
            [['save_file_model'], 'integer'],
            ['file', 'file', 'skipOnEmpty' => false],
            ['size', 'validateSize'],
        ];
    }
    
    public function validateSize($attribute, $params, $validator)
    {   
        $bytes = $this->formatBytes($this->max_size);
        if ($this->$attribute > $bytes['value'] || in_array($bytes['unit'],['B', 'KB', 'MB'])) {
            $this->addError($attribute, "Max file size is Mb(s).");
        }
    }
    
    public function formatBytes($bytes, $precision = 2) {

        $units = array('B', 'KB', 'MB', 'GB', 'TB'); 

        $bytes = max($bytes, 0); 
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
        $pow = min($pow, count($units) - 1); 

        return ['value'=> round($bytes, $precision),'unit'=>$units[$pow]]; 
        
    }  
    
    /**
     * Upload one file to server
     * If upload is successful, return array, on failure return false
     * @param array $params
     * @return array|false
     */
    public function uploadFile($params = []) {
        
        $folder = \Yii::getAlias('@uploadFolder/');
        $url = \Yii::getAlias('@web/files/');

        $url_file = '';
        $duration = null;
        $urlThumb =  '';
        $thumb = false;
        $thumbSize = ['w' => 150, 'h' => 150];
        $folderByType = false;
        $quality = 60;
        $filePath = $folder;
        $filePathThumb = '';

        if(!empty($params)){
            if(isset($params['extensions'])){
                $this->extensions = $params['extensions'];
            }
            if(isset($params['folder'])){
                $folder = $params['folder'];
            }
            if(isset($params['folderByType'])){
                $folderByType = $params['folderByType'];
            }
            if(isset($params['thumb'])){
                $thumb = $params['thumb'];
            }
            if(isset($params['thumbSize'])){
                $thumbSize = $params['thumbSize'];
            }
            if(isset($params['quality'])){
                $quality = $params['quality'];
            }
        }
    
        if ($this->validate()) {
            
            $ext = $this->file->extension;

            if(!empty($this->file_name)){
                $name = "{$this->file_name}.{$ext}";
            }else{
                $name = 'file_' . date('dmYhims').\Yii::$app->security->generateRandomString(6) . ".{$ext}";
            }

            if (in_array($ext, ['png', 'jpg', 'gif', 'jpeg', 'bmp', 'jfif'])) {
                
                $type = 'img';
                $path = $folderByType ? $folder. 'images' : $folder;
                $filePath = $path . '/'. $name;
                
                if(!file_exists($path)){
                    FileHelper::createDirectory($path);
                }
            
                $url_file = $folderByType ? $url. 'images/' . $name  : $url .$name;

                $errors[] = $this->file->saveAs($filePath, ['quality' => $quality]);

                if ($thumb) {
                    $pathThumb =  $folderByType ? $folder. 'images/thumbs/' : $folder . 'thumbs/';
                    $filePathThumb = $pathThumb . $name;
                    if(!file_exists($pathThumb)){
                        FileHelper::createDirectory($pathThumb);
                    }
                    
                    $image_size = getimagesize($filePath);
                    $major = $image_size[0];//width
                    $min = $image_size[1];//height
                    $mov = ($major - $min)/2;
                    $point = [$mov, 0];

                    if($major < $min){
                        $major = $image_size[1];
                        $min = $image_size[0];
                        $mov = ($major - $min)/2;
                        $point = [0, $mov];
                    }

                    $urlThumb = $folderByType ? $url. 'images/thumbs/' . $name : $url. 'thumbs/' . $name;

                    $errors[] = Image::crop($filePath, $min, $min,$point)
                    ->save($filePathThumb, ['quality' => 100]);
                }
                
            } else if(in_array($ext, ['mp4','mkv','mpeg','avi'])){
                
                $type = 'vid';
                $video_name = $name;
                if($this->convert_video){
                    $video_name =  str_replace('.','_',$name) . '.' . $this->convert_video_format;
                }

                $ext = $this->convert_video_format;

                $url_file = $folderByType ? $url. 'videos/' . $video_name : $url  .$video_name;
                $path = $folderByType ? $folder. 'videos' : $folder;
                $filePath = $path . '/' . $video_name;
                $fileTemp = $path . '/' . $name;
                
                if(!file_exists($path)){
                    FileHelper::createDirectory($path);
                }

                if($this->convert_video && $ext != 'mp4'){
                    $errors[] = $this->file->saveAs($fileTemp , ['quality' => $quality]); 
                    $ffmpeg = FFMpeg::create();
                    $video = $ffmpeg->open($fileTemp);
                    $video->save(new X264(), $filePath);
                    unlink($fileTemp);
                }else{
                    $errors[] = $this->file->saveAs($filePath, ['quality' => $quality]);
                }

                if ($thumb) {
                    $sec = 2;
                    $video_thumb_name = str_replace('.','_',$name).'.jpg';
                    $urlThumb = $folderByType ? $url. 'videos/thumbs/' . $video_thumb_name : $url. 'thumbs/' . $video_thumb_name;
                    $pathThumb =  $folderByType ? $folder. 'videos/thumbs/' : $folder . 'thumbs/';
                    $filePathThumb = $pathThumb . $video_thumb_name;
                    
                    if(!file_exists($pathThumb)){
                        FileHelper::createDirectory($pathThumb);
                    }   
                    
                    $ffmpeg = FFMpeg::create();
                    $video = $ffmpeg->open($filePath);
                    $frame = $video->frame(TimeCode::fromSeconds($sec));

                    $frame->save($filePathThumb);
                    
                    $image_size = getimagesize($filePathThumb);
                    $major = $image_size[0];//width
                    $min = $image_size[1];//height
                    $mov = ($major - $min)/2;
                    $point = [$mov, 0];

                    if($major < $min){
                        $major = $image_size[1];
                        $min = $image_size[0];
                        $mov = ($major - $min)/2;
                        $point = [0, $mov];
                    }

                    $errors[] = Image::crop($filePathThumb, $min, $min,$point)
                    ->save($filePathThumb, ['quality' => 100]);

                }

                $ffprobe = FFProbe::create();
                $duration = $ffprobe
                    ->format($filePath) // extracts file informations
                    ->get('duration');

            } else {
                
                $type = 'doc';
                $path = $folderByType ? $folder. 'docs' : $folder;
                $filePath = $path . '/' . $name;
                
                if(!file_exists($path)){
                    FileHelper::createDirectory($path);
                }
                
                $url_file = $folderByType ? $url. 'docs/' . $name  : $url .$name;
                $errors[] = $this->file->saveAs($filePath);
            }

            return [
                'name' => $name,
                'folder_id' => $this->folder_id,
                'description' => $this->file->name,
                'path' => $filePath,
                'pathThumb' => $filePathThumb,
                'url'=> $url_file,
                'urlThumb'=> $urlThumb,
                'preview'=> $url.$urlThumb,
                'extension' => $ext,
                'type' => $type,
                'duration' => intval($duration),
                'size' => $this->file->size,
                'created_at'=> \Yii::$app->formatter->asDate(date('Y-m-d h:i:s'))
            ];
        }else {
           return false;
        }
        
    }
}