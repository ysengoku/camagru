import { signupService } from "../../services";
import { HEADERS } from "../../utils/constants";
import { IncomingMessage, ServerResponse } from "node:http";

export function signupController(req: IncomingMessage, res: ServerResponse) {
  if (req.method !== 'POST') {
    res.writeHead(405, HEADERS.JSON);
    res.end(JSON.stringify({ success: false, message: 'Method Not Allowed' }));
    return;
  }

  let requestBody = '';
  // Node.jsのhttpモジュールでは、POSTデータはチャンク（小さいデータの塊）で届く。
  // このイベントはデータが届くたびに呼ばれるので、変数requestBodyに順に追加していく。
  req.on('data', chunk => { requestBody += chunk });
  // 全てのデータ受信が完了したタイミングでendイベントが呼ばれる。
  req.on('end', async () => {
    let result;
    try {
      const data = JSON.parse(requestBody);
      console.log(data);
      result = await signupService(data);
    } catch (error) {
      result = { success: false, message: 'Invalid data' };
    }
      if (result.success) {
        res.writeHead(201, HEADERS.JSON);
        res.end(JSON.stringify({ success: true, message: 'User created' }));
        return;
      }
      res.writeHead(400, HEADERS.JSON);
      res.end(JSON.stringify(result));
  })
}
