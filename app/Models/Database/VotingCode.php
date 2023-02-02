<?php
namespace App\Models\Database;

use Illuminate\Database\Eloquent\Model;

class VotingCode extends Model
{
    //
    protected $table = 'voting_codes';
    
    protected $fillable = [ 'code' ];
    
    protected $dates = [ ];
    
    protected $hidden = [ ];
    
    private static $allowed_chars = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789"; 
    
    public function user() {
        return $this->belongsTo('App\Models\Database\User', 'user_id');
    }
    
    public static function generate($size) {
        while(true) {
            $result = "";
            $bytes = random_bytes($size);
            $max = strlen(self::$allowed_chars);
            for($i = 0; $i < $size; $i++) {
                if($i > 0 && $i % 3 == 0) {
                    $result .= '-';
                }
                $char = ord($bytes[$i]) % $max;
                $result .= self::$allowed_chars[$char];
            }
            $codes = self::where('code', $result)->get();
            if($codes->isEmpty()) {
                break;
            }
        }
        return new self(['code' => $result]);
    }
    
}

