<?php
namespace App; use Illuminate\Database\Eloquent\Model; class File extends Model { protected $guarded = array(); public $timestamps = false; function deleteFile() { try { Storage::disk($this->driver)->delete($this->path); } catch (\Exception $sp8e3e91) { \Log::error('File.deleteFile Error: ' . $sp8e3e91->getMessage(), array('exception' => $sp8e3e91)); } } public static function getProductFolder() { return 'images/product'; } }