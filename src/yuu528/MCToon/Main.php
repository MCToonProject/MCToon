<?php
namespace yuu528\MCToon;

use pocketmine\plugin\PluginBase;

use pocketmine\Server as Srv;

use pocketmine\item\Item;

use pocketmine\entity\Entity;

use pocketmine\event\player\PlayerInteractEvent as PTap;
use pocketmine\event\player\PlayerJoinEvent as PJoin;
use pocketmine\event\player\PlayerQuitEvent as PQuit;
use pocketmine\event\player\PlayerChatEvent as PChat;

use pocketmine\event\block\BlockPlaceEvent as BPlace;
use pocketmine\event\block\BlockBreakEvent as BBreak;
use pocketmine\event\Listener;

use pocketmine\utils\Config as Conf;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\CommandExecutor;

class Main extends PluginBase implements Listener{
	function onEnable(){
		Srv::getInstance()->getPluginManager()->registerEvents($this,$this);
		Srv::getInstance()->getLogger()->info("[MCToon]有効");

		if(!file_exists($this->getDataFolder()."players")){
			mkdir($this->getDataFolder()."players", 0755, true);
		}
	}

	function onDisable(){
		Srv::getInstance()->getLogger()->info("[MCToon]無効");
	}

	function onTap(PTap $e){
		if(isset($this->nologin[$e->getPlayer()->getName()]) or isset($this->noreg[$e->getPlayer()->getName()])){
			$e->setCancelled(true);
		}
		/*$item = $e->getItem();
		$block = $e->getBlock();*/
	}


	function onJoin(PJoin $e){
		$p = $e->getPlayer();
		$p->sendMessage("§aようこそ! MCToonServerへ!");
		$p->getInventory()->clearAll();
		$pdata = new Conf($this->getDataFolder()."players\\".$p->getName().".dat", Conf::YAML);
		if(!file_exists($this->getDataFolder()."players\\".$p->getName().".dat")){
			$p->setDataProperty(Entity::DATA_NO_AI, Entity::DATA_TYPE_BYTE, 1);
			$p->sendMessage("§bまず最初に/reg <パスワード> でパスワード(プレイヤー名以外)を設定してください。");
			$this->noreg[$p->getName()] = true;
			return;
		}

		if($pdata->get("ip") !== $p->getAddress()){
			$p->setDataProperty(Entity::DATA_NO_AI, Entity::DATA_TYPE_BYTE, 1);
			$p->sendMessage("§bIPが変わったようです。/login <パスワード> でログインしてください。");
			$this->nologin[$p->getName()] = true;
			return;
		}
	}

	function onQuit(PQuit $e){
		if(isset($this->nologin[$e->getPlayer()->getName()]) or isset($this->noreg[$e->getPlayer()->getName()])){
			unset($this->nologin[$e->getPlayer()->getName()]);
			unset($this->noreg[$e->getPlayer()->getName()]);
		}
	}

	function onPlace(BPlace $e){
		if(isset($this->nologin[$e->getPlayer()->getName()]) or isset($this->noreg[$e->getPlayer()->getName()])){
			$e->setCancelled(true);
		}
	}

	function onBreak(BBreak $e){
		if(isset($this->nologin[$e->getPlayer()->getName()]) or isset($this->noreg[$e->getPlayer()->getName()])){
			$e->setCancelled(true);
		}
	}

	function onChat(PChat $e){
		if(isset($this->nologin[$e->getPlayer()->getName()]) or isset($this->noreg[$e->getPlayer()->getName()])){
			$e->setCancelled(true);
		}
	}

	function giveAllBuki($p){
		$p->sendMessage("§b使いたいブキを持って参加看板をタップしてください。");
		$item = Item::get(280, 0, 1);//パブロ
		$item = Item::get(274, 0, 1);//ローラー

		//ここにブキをプレイヤーに渡すコードを追加
	}

	function onCommand(CommandSender $p, Command $command, $label, array $ar){
		switch(strtolower($command->getName())){
			case "reg":
				if(!isset($ar[0])) return false;
				if(isset($this->noreg[$p->getName()])){
					if($this->noreg[$p->getName()] !== true){
						$p->sendMessage("§4あなたは現在このコマンドを使用することができません");
						return true;
						break;
					}
				}else{
					$p->sendMessage("§4あなたは現在このコマンドを使用することができません");
					return true;
					break;
				}
				if(isset($ar[1])){
					$p->sendMessage("§4空白は使わないでください");
					return true;
					break;
				}
				if($p->getName() === $ar[0]){
					$p->sendMessage("§4プレイヤー名は使わないでください");
					return true;
					break;
				}
				if(strpos($ar[0], ",") !== false or strpos($ar[0], ":")){
					$p->sendMessage("','と':' は使わないでください");
					return true;
					break;
				}
				$pdata = new Conf($this->getDataFolder()."players\\".$p->getName().".dat", Conf::YAML);
				$pdata->set("pass", $ar[0]);
				$pdata->set("ip", $p->getAddress());
				$pdata->save();
				$p->setDataProperty(Entity::DATA_NO_AI, Entity::DATA_TYPE_BYTE, 0);
				$p->sendMessage("§bログイン認証が完了しました。");
				unset($this->noreg[$p->getName()]);
				Main::giveAllBuki($p);
				return true;
				break;
			//close "reg"

			case "login":
				if(!isset($ar[0])) return false;
				if(isset($this->nologin[$p->getName()])){
					if($this->nologin[$p->getName()] !== true){
						$p->sendMessage("§4あなたは現在このコマンドを使用することができません");
						return true;
						break;
					}
				}else{
					$p->sendMessage("§4あなたは現在このコマンドを使用することができません");
					return true;
					break;
				}

				$pdata = new Conf($this->getDataFolder()."players\\".$p->getName().".dat", Conf::YAML);
				if($ar[0] === $pdata->get("pass")){
					$p->setDataProperty(Entity::DATA_NO_AI, Entity::DATA_TYPE_BYTE, 0);
					$pdata->set("ip", $p->getAddress());
					$pdata->save();
					$p->sendMessage("§bログイン認証が完了しました。");
					unset($this->nologin[$p->getName()]);
					Main::giveAllBuki($p);
					return true;
					break;
				}else{
					$p->sendMessage("§4パスワードが異なります。忘れた場合は運営までお問い合わせください。");
					return true;
					break;
				}
				$p->sendMessage("§4エラーが発生しました。エラーコードを運営までお問い合わせください。\n§4コード: 0140(If Check Not Working)");//IF文が働いていない
				return true;
				break;
			//close "login"
		}
	}
}