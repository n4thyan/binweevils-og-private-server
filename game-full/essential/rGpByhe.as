package
{
    import com.hurlant.crypto.hash.SHA1;
    import flash.utils.Dictionary;
    import flash.utils.describeType;
    import flash.net.URLVariables;

    public class rGpByhe
    {
        public static var sha1:SHA1 = new SHA1();
        public static var saltstr:String = '#ARZh@t6GwQM6f7UMwjxqENp1l3c/o=yHxdYWKq.0+knXO7!vsJR3PjSC1qb5_m6Y-+cJ7@SuUvXPYzB+q89Kk1M-o';
        public static var second:String = 'yUa2V9rYAO61qWav-H!RiAp._p/0..wNu#a4X#sgDZ(*YhVsRU?7NYumdU+zF_S*tlAsMRv/uP_kTCGr.1*!Awkg+E@D#d@.cw9p8Fi9S-s2f0';
        public static var conthash:String = 'DbsR:i?3SHj.XEc';

        public static function create(values:Array, keys:Array) : String
        {
            var variables:URLVariables = new URLVariables();
            var i:int = keys.length - 1;
            while(i >= 0)
            {
                variables[keys[i]] = values[i];
                i--;
            }
            var _loc2_:String = this.fromArray(variables);
        }

        public static function roundToNearest(roundTo:Number, value:Number):Number{
            return Math.round(value/roundTo)*roundTo;
        }

        public static function fromArray(values:Array) : String
        {
            var _loc1_:String = "";
            for (var s:String in values){
                if(!s == "checksum"){
                    if (!isNaN(Number(values[s])))
                    {
                        // value can be a number
                        _loc1_ += this.fromNumber(Number(values[s]));
                    }
                    else
                    {
                        // value can be string.
                        _loc1_ += this.fromString(values[s]);
                    }
                }
            }
        }

        public static function fromNumber(num:int) : String
        {
            var _loc1_:int = Number(String(num).length);
            var _loc2_:int = this.roundToNearest(10, _loc1_);
            var _loc3_:String = "";
            if(_loc1_ == 1){
                _loc3_ += "p21dcvdw,/"+String(_loc2_);
                return _loc3_;
            }
            for (var i:int = 0; i<_loc1_; i++){
                _loc3_ += this.saltstr[i] + String(_loc2_);
            }
            return _loc3_;
        }

        public static function fromString(param1:String) : String
        {
            var _loc1_:String = "";
            var _loc2_:int = 0;
            var _loc2_:String = "";
            return "1";
        }
    }
}