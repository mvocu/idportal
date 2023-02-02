<?php
namespace App\Http\Controllers;

use App\Interfaces\VotingCodeManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VotingCodeController extends Controller
{
    protected $voting_code_mgr;
    
    public function __construct(VotingCodeManager $voting_code_mgr)
    {
        $this->voting_code_mgr = $voting_code_mgr;
        $this->middleware(['auth.eidp:MojeID', 'auth.eidp:eIdentita', 'auth']);
    }
    
    public function showCode(Request $request)
    {
        $user = Auth::user()->getDatabaseUser();
        $code = $this->voting_code_mgr->getActiveVotingCode($user);
        return view('votingcode', ['code' => $code]);
    }
    
    public function getCode(Request $request) 
    {
        return view('votingform');
    }
    
    public function declare(Request $request)
    {
        $user = Auth::user()->getDatabaseUser();
        if($request->get("consent_check", "no") != "agree") {
            return back()
                ->withErrors(['failure' => 'You have to agree with the declaration to obtain voting code.']);
        }
        if(!$this->voting_code_mgr->assignVotingCode($user)) {
            return back()
                ->withErrors(['failure' => 'Could not assign new voting code']);
        }
        return redirect()->route('voting.show')->with(['status' => __('Declaration accepted.')]);
    }
    
}

