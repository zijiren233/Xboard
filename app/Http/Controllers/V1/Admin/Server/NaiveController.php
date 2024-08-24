<?php

namespace App\Http\Controllers\V1\Admin\Server;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Models\ServerNaive;
use Illuminate\Http\Request;

class NaiveController extends Controller
{
    public function save(Request $request)
    {
        $params = $request->validate([
            'show' => '',
            'name' => 'required',
            'group_id' => 'required|array',
            'route_id' => 'nullable|array',
            'parent_id' => 'nullable|integer',
            'host' => 'required',
            'port' => 'required',
            'server_port' => 'required',
            'tags' => 'nullable|array',
            'excludes' => 'nullable|array',
            'server_name' => 'nullable',
        ],[
            'name.required' => '节点名称不能为空',
            'group_id.required' => '权限组不能为空',
            'host.required' => '节点地址不能为空',
            'port.required' => '连接端口不能为空',
            'server_port' => '服务端口不能为空',
        ]);

        if ($request->input('id')) {
            $server = ServerNaive::find($request->input('id'));
            if (!$server) {
                return $this->fail([400202, '服务器不存在']);
            }
            try {
                $server->update($params);
            } catch (\Exception $e) {
                \Log::error($e);
                return $this->fail([500,'保存失败']);
            }
            return $this->success(true);
        }

        if (!ServerNaive::create($params)) {
            return $this->fail([500,'创建失败']);
        }

        return $this->success(true);
    }

    public function drop(Request $request)
    {
        if ($request->input('id')) {
            $server = ServerNaive::find($request->input('id'));
            if (!$server) {
                return $this->fail([400202,'节点ID不存在']);
            }
        }
        return $this->success($server->delete());
    }

    public function update(Request $request)
    {
        $request->validate([
            'show' => 'in:0,1'
        ], [
            'show.in' => '显示状态格式不正确'
        ]);
        $params = $request->only([
            'show',
        ]);

        $server = ServerNaive::find($request->input('id'));

        if (!$server) {
            return $this->fail([400202,'该服务器不存在']);
        }
        try {
            $server->update($params);
        } catch (\Exception $e) {
            \Log::error($e);
            return $this->fail([500,'保存失败']);
        }

        return $this->success(true);
    }

    public function copy(Request $request)
    {
        $server = ServerNaive::find($request->input('id'));
        $server->show = 0;
        if (!$server) {
            return $this->fail([400202,'服务器不存在']);
        }
        ServerNaive::create($server->toArray());
        return $this->success(true);
    }
}
