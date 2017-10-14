<?php

namespace App\Http\Controllers;

use App\Http\Requests\EditUserRequest;
use App\Http\Requests\FormUserRequest;
use App\Http\Requests\LoginRequest;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use \DataTables;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function getLogin()
    {
        return v('pages.login');
    }

    public function postLogin(LoginRequest $request)
    {
        $logins =   json_decode(settings('loginas', json_encode(['id'])), 'true');
        if(in_array('username',$logins))
            $loginUsername   =   auth()->attempt(['name'=>$request->input('id'),'password'=>$request->input('password')], $request->has('remember'));
        if(in_array('email',$logins))
            $loginEmail  =   auth()->attempt(['email'=>$request->input('id'),'password'=>$request->input('password')], $request->has('remember'));
        if(in_array('id',$logins))
            $loginId  =   auth()->attempt(['id'=>$request->input('id'),'password'=>$request->input('password')], $request->has('remember'));
        if(!empty($loginEmail) || !empty($loginUsername) || !empty($loginId)){
            return redirect()->to(asset('/'));
        } else {
            return redirect()->back()->withErrors(trans('auth.failed'));
        }
    }


    public function getList() {
        return v('users.list');
    }
    public function dataList() {
        $data   =   User::with('group','branch');

        $result = Datatables::of($data)
            ->addColumn('group', function(User $user) {
                return $user->group->name;
            })->addColumn('branch', function(User $user) {
                return $user->branch->name;
            })->addColumn('manage', function($user) {
                return a('config/user/del', 'id='.$user->id,trans('g.delete'), ['class'=>'btn btn-xs btn-danger'],'#',"return bootbox.confirm('".trans('system.delete_confirm')."', function(result){if(result==true){window.location.replace('".asset('config/user/del?id='.$user->id)."')}})").'  '.a('config/user/edit', 'id='.$user->id,trans('g.edit'), ['class'=>'btn btn-xs btn-default']);
            })->rawColumns(['manage']);


        return $result->make(true);
    }

    public function getCreate()
    {
        return v('users.create');
    }

    public function postCreate(FormUserRequest $request)
    {
        $data   =   new User();
        $data->name   =   $request->name;
        $data->email    =   $request->email;
        $data->password =   Hash::make($request->password);
        $data->branch_id    =   $request->branch_id;
        $data->group_id =   $request->group_id;
        $data->created_at   =   Carbon::now();
        $data->save();
        set_notice(trans('users.add_success'), 'success');
        return redirect()->back();
    }
    public function getEdit()
    {
        $data   =   User::find(request('id'));
        if(!empty($data)){
            return v('users.edit', compact('data'));
        }else{
            set_notice(trans('system.not_exist'), 'warning');
            return redirect()->back();
        }
    }
    public function postEdit(EditUserRequest $request)
    {
        $data   =   User::find($request->id);
        if(!empty($data)){
            $data->name   =   $request->name;
            $data->email    =   $request->email;
            if($request->has('password'))
                $data->password =   Hash::make($request->password);
            $data->branch_id    =   $request->branch_id;
            $data->group_id =   $request->group_id;
            $data->save();
            set_notice(trans('system.edit_success'), 'success');
        }else
            set_notice(trans('system.not_exist'), 'warning');
        return redirect()->back();
    }
    public function getDelete()
    {
        $data   =   User::find(request('id'));
        if(!empty($data)){
            $data->delete();
            set_notice(trans('system.delete_success'), 'success');
        }else
            set_notice(trans('system.not_exist'), 'warning');
        return redirect()->back();
    }

}
