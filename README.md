telegram 机器人
============

一个 PHP 写的 telegram 机器人

Bot Commands
------------
<table>
  <thead>
    <tr>
      <td><strong>Name</strong></td>
      <td><strong>Description</strong></td>
      <td><strong>Usage</strong></td>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>help</td>
      <td>Help plugin. Get info from other plugins.</td>
      <td>
        /help - Show list of plugins.<br/>
        /help all - Show all commands for every plugin.<br/>
        /help [plugin name] - Commands for that plugin.
      </td>
    </tr>
    <tr>
      <td>bot</td>
      <td>询问图灵小机器人.</td>
      <td>
        /bot info: 请求图灵的机器人接口，并返回回答。<br/>
        Request Turing robot, and return the results. Only support Chinese.<br/>
        升级链接|Upgrade link:http://www.tuling123.com/openapi/record.do?channel=98150<br/>
        图灵机器人注册邀请地址，每有一个用户通过此地址注册账号，增加本接口可调用次数 1000次/天。<br/>
        Turing robot registration invitation address, each user has a registered account through this address, increase the number of calls this interface can be 1000 times / day. Translation from Google!<br/>
      </td>
    </tr>
    <tr>
      <td>google</td>
      <td>Searches Google and send results</td>
      <td>/google - Searches Google and send results.</td>
    </tr>
    <tr>
      <td>img</td>
      <td>Random search an image with Google API.</td>
      <td>/img info: Random search an image with Google API.</td>
    </tr>
    <tr>
      <td>stats</td>
      <td>Plugin to update user stats.</td>
      <td>
        /stats - Returns a list of Username [telegram_id]: msg_num only top.10.<br/>
        /stats 20150528 - Returns this day stats<br/>
        /stats all: Returns All days stats.<br/>
        /stats 20150528 10: Returns a list only top 10.<br/>
        /state user_id: Returns this user All days stats.<br/>
      </td>
    </tr>
    <tr>
      <td>echo</td>
      <td>Simplest plugin ever!</td>
      <td>/echo [whatever]: echoes the msg.</td>
    </tr>
  </tbody>
</table>

Installation
------------
1. clone 项目到本地
2. 配置 redis
3. 设置 Telegram 回掉地址，地址为 项目地址 + ?token={telegram_bot.token}
4. 开始玩

Contact me
------------
You can contact me [via Telegram](https://telegram.me/drayc) but if you have an issue please [open](https://github.com/DrayChou/tgbot-php/issues) one.
