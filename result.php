<?php 

ini_set('log_errors','on');
ini_set('error_log','php.log');
session_start();

// ハリネズミくんの仲間たちの格納用
$animals = array();

// 抽象クラス（生き物クラス）
abstract class Creature{
    protected $name;
    protected $money;
    // 名前
    public function setName($str){
        $this->name = $str;
    }
    public function getName(){
        return $this->name;
    }
    // お小遣い
    public function setMoney($num){
        $this->money = $num;
    }
    public function getMoney(){
        return $this->money;
    }
}

// ハリネズミくんクラス
class Harinezumi extends Creature{
    // プロパティ
    protected $hp;
    protected $helpMin;
    protected $helpMax;
    // インスタンス生成時に自動的に呼ばれるコンストラクタ
    public function __construct($name,$hp,$helpMin,$helpMax,$money){
        $this->name = $name;
        $this->hp = $hp;
        $this->helpMin = $helpMin;
        $this->helpMax = $helpMax;
        $this->money = $money;
    }
    // 体力
    public function setHp($num){
        $this->hp = $num;
    }
    public function getHp(){
        return $this->hp;
    }

    public function help($targetWork){
        // ハリネズミくんのお手伝いパワー
        $helpPoint = mt_rand($this->helpMin,$this->helpMax);
        //10分の1の確率でお手伝いパワーが1.5倍に！
        if(!mt_rand(0,9)){
            $helpPoint = $helpPoint * 1.5;
            $helpPoint = (int)$helpPoint;  //0の場合falseで返ってきてしまうのでint型にする
            History::set($this->getName().'のパワーMAX！！');
        }
        $targetWork->setWork($targetWork->getWork()-$helpPoint);
        History::set('・・お手伝い中('.$helpPoint.'%完了)・・');
    }
}

// ハリネズミくんの仲間たちクラス
class Animal extends Creature{
    // プロパティ
    protected $work;
    protected $img;
    protected $workName;
    protected $exhaustMin;
    protected $exhaustMax;
    // コンストラクタ
    public function __construct($name,$work,$img,$workName,$exhaustMin,$exhaustMax,$money){
        $this->name = $name;
        $this->work = $work;
        $this->img = $img;
        $this->workName = $workName;
        $this->exhaustMin = $exhaustMin;
        $this->exhaustMax = $exhaustMax;
        $this->money = $money;
    }
    // お手伝いの量
    public function setWork($num){
        $this->work = $num;
    }
    public function getWork(){
        return $this->work;
    }
    // 仲間たちの画像（読み取り専用）
    public function getImg(){
        return $this->img;
    }
    // お手伝いの名前（読み取り専用）
    public function getWorkName(){
        return $this->workName;
    }


    public function exhaust($obj){
        // お手伝いすることで体力が減る
        $exhaustHp = mt_rand($this->exhaustMin,$this->exhaustMax);
        //7分の1の確率で体力消耗が1.5倍に
        if(!mt_rand(0,6)){
            $exhaustHp = $exhaustHp * 1.5;
            $exhaustHp = (int)$exhaustHp;
        }
        $obj->setHp($obj->getHp() - $exhaustHp);
        History::set($obj->getName().'のHPが '.$exhaustHp.' 減った');
    }

    public function pocketMoney($obj){
        // お手伝い完了するとお小遣いがもらえる
        $getPocketMoney = $this->money;
        $obj->setMoney($obj->getMoney() + $getPocketMoney);
        History::set($_SESSION['animal']->getName().'のお手伝いが完了した！！<br>ご褒美に '.$getPocketMoney.' 円もらった♡');
    }
}

// インターフェース
interface HistoryInterface{
    public static function set($str);
    public static function clear();
}

// 履歴管理クラス（インスタンス化して複数つくる必要がないクラスなのでstaticにする）
class History implements HistoryInterface{
    public static function set($str){
        if(empty($_SESSION['history'])) $_SESSION['history']='';
        $_SESSION['history'] = $str.'<br>';
    }
    public static function clear(){
        unset($_SESSION['history']);
    }
}

// インスタンス生成
$harinezumi = new Harinezumi('ハリネズミくん',100,20,50,0);
$animals[] = new Animal('トイプーちゃん',100,'img/toy.png','お花の水やり',3,5,100);
$animals[] = new Animal('スコティッシュちゃん',100,'img/scottish.png','引越し',10,20,1000);
$animals[] = new Animal('三毛猫くん',100,'img/mike.png','お風呂の掃除',8,10,500);
$animals[] = new Animal('あらちゃん',100,'img/ara.png','ご飯の準備',5,8,100);
$animals[] = new Animal('文鳥くん',100,'img/buncho.png','お部屋の片付け',12,15,200);
$animals[] = new Animal('フレブルくん',100,'img/french.png','買い物',7,10,100);
$animals[] = new Animal('牛くん',100,'img/ushi.png','乳しぼり',8,12,300);
$animals[] = new Animal('キリンくん',100,'img/kirin.png','お洗濯',5,12,100);
$animals[] = new Animal('ペンギンくん',100,'img/pen.png','魚釣り',8,13,600);
$animals[] = new Animal('柴犬くん',100,'img/siba.png','ゴミ捨て',5,8,400);

// 仲間たち誕生
function createAnimal(){
    global $animals;
    $animal = $animals[mt_rand(0,9)];
    $_SESSION['animal'] = $animal;
}
// ハリネズミくん誕生
function createHarinezumi(){
    global $harinezumi;
    $_SESSION['harinezumi'] = $harinezumi;
}

// 初期化
function init(){
    History::clear();
    History::set('初期化します');
    $_SESSION['moneyCount'] = 0;
    createAnimal();
    createHarinezumi();
}

// ゲームオーバー
function gameOver(){
    $_SESSION = array();
}

// POST送信されていた場合
if(!empty($_POST)){
    $helpFlg = (!empty($_POST['help'])) ? true : false;
    $startFlg = (!empty($_POST['start'])) ? true : false;
    error_log('POSTされた！');

    // リスタートボタンを押した場合
    if($startFlg){
        header("Location:index.php");
        History::set('ゲームスタート！');
        init();
    }else{
        // お手伝いする（いいよ！）ボタンを押した場合
        if($helpFlg){
            // お手伝いする
            $_SESSION['harinezumi']->help($_SESSION['animal']);

            // ハリネズミくんの体力が減る
            $_SESSION['animal']->exhaust($_SESSION['harinezumi']);

            // ハリネズミくんの体力が0以下になったらゲームオーバー
            if($_SESSION['harinezumi']->getHp() <= 0){
                header("Location:result.php");  //マイページへ
            }else{
                // お手伝いが完了したらお小遣いをもらい、別の仲間を出現させる
                if($_SESSION['animal']->getWork() <= 0){
                    $_SESSION['animal']->pocketMoney($_SESSION['harinezumi']);
                    $_SESSION['moneyCount'] = $_SESSION['moneyCount'] + $_SESSION['animal']->getMoney();
                    createAnimal();
                }
            }
        }else{
            // 断るボタンを押した場合
            History::set('断った');
            createAnimal();
        }

    }
    $_POST = array();
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ハリネズミくんのお手伝い</title>
    <link rel="stylesheet" href="style.css" type="text/css">
</head>

<body>
    <div class="wrapper">
        <div id="main">
                <div class="sun-img">
                    <img src="img/sun.png">
                </div>
                <div class="start-text">
                    <h1>お疲れさまでした♡</h1>
                    <p>お手伝い終了！</p>
                    <p>もらったお小遣い：<?php echo $_SESSION['moneyCount']; ?></p>
                    <form method="post">
                        <input type="submit" name="start" value="リスタート" class="btn">
                    </form>
                </div>
                <div class="harinezumi-img">
                    <img src="img/harinezumi.png">
                </div>
        </div>
            <footer id="footer"></footer>
    </div>

</body>
</html>
