<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\service\ResponseSender as Response;
use App\Models\admin\About;
use App\Models\admin\PrivacyPolicy;
use App\Models\admin\Term;

use Validator;

class CmsController extends Controller
{
    public function updateAbout(Request $request)
    
    {
        $fields    = $request->input();
        $validator = Validator::make($request->all(),
            [
                'title_en' => 'nullable|required_without:title_so',
                'title_so' => 'nullable|required_without:title_en',
                'content_en' => 'nullable|required_without:content_so',
                'content_so' => 'nullable|required_without:content_en',
            ],
            [
                'title_en.required_without' =>  __('error2.title_required_without'),
                'title_so.required_without' =>  __('error2.title_required_without'),
                'content_en.required_without' =>  __('error2.content_required_without'),
                'content_so.required_without' =>  __('error2.content_required_without'),
            ]
            );
            if ($validator->fails()) 
                {
                    $errors = collect($validator->errors());
                    $res    = Response::send('false', $data = [], $message = $errors, $code = 422);
                } else 
                {
                  $about = About::find(1);
                  $about->title_en = $fields['title_en'];
                  $about->title_so = $fields['title_so'];
                  $about->content_en = $fields['content_en'];
                  $about->content_so = $fields['content_so'];
                  $result = $about->save();
                  if ($result) 
                    {
                    $res  = Response::send('true', [], $message = __('success2.update_about'), $code = 200);
                    } 
                  else 
                    {
                    $res = Response::send('false',[], $message = __('error2.update_about'), $code = 400);
                    }
                } 
                return $res;
                }
    public function updatePolicy(Request $request)
    
        {
         $fields    = $request->input();
         $validator = Validator::make($request->all(),
            [
                'title_en' => 'nullable|required_without:title_so',
                'title_so' => 'nullable|required_without:title_en',
                'policy_en' => 'nullable|required_without:policy_so',
                'policy_so' => 'nullable|required_without:policy_en',
            ],
            [
                'title_en.required_without' =>  __('error2.title_required_without'),
                'title_so.required_without' =>  __('error2.title_required_without'),
                'policy_en.required_without' =>  __('error2.policy_required_without'),
                'policy_so.required_without' =>  __('error2.policy_required_without'),
             ]
                 );
            if ($validator->fails()) 
                {
                    $errors = collect($validator->errors());
                    $res    = Response::send('false', $data = [], $message = $errors, $code = 422);
                        } else 
                        {
                            $policy = PrivacyPolicy::find(1);
                            $policy->title_en = $fields['title_en'];
                            $policy->title_so = $fields['title_so'];
                            $policy->policy_en = $fields['policy_en'];
                            $policy->policy_so = $fields['policy_so'];
                            $result = $policy->save();
                              if ($result) 
                                {
                                $res  = Response::send('true', [], $message = __('success2.update_policy'), $code = 200);
                                } 
                              else 
                                {
                                $res = Response::send('false',[], $message = __('error2.update_policy'), $code = 400);
                                }
                            } 
                            return $res;
                            }
     public function updateTerm(Request $request)
    
        {
             $fields    = $request->input();
             $validator = Validator::make($request->all(),
                [
                    'title_en' => 'nullable|required_without:title_so',
                    'title_so' => 'nullable|required_without:title_en',
                    'term_en' => 'nullable|required_without:term_so',
                     'term_so' => 'nullable|required_without:term_en',
                 ],
                [
                     'title_en.required_without' =>  __('error2.title_required_without'),
                    'title_so.required_without' =>  __('error2.title_required_without'),
                     'term_en.required_without' =>  __('error2.term_required_without'),
                     'term_so.required_without' =>  __('error2.term_required_without'),
                ]
                    );
            if ($validator->fails()) 
                 {
                     $errors = collect($validator->errors());
                    $res    = Response::send('false', $data = [], $message = $errors, $code = 422);
                 } else 
                {
                    $term = Term::find(1);
                    $term->title_en = $fields['title_en'];
                    $term->title_so = $fields['title_so'];
                    $term->term_en = $fields['term_en'];
                    $term->term_so = $fields['term_so'];
                    $result = $term->save();
                    if ($result) 
                    {
                        $res  = Response::send('true', [], $message = __('success2.update_term'), $code = 200);
                     } 
                    else 
                    {
                        $res = Response::send('false',[], $message = __('error2.update_term'), $code = 400);
                    }
                        } 
                    return $res;
                }

public function indexAbout()
    {
    $about = About::select('about.*')->get(); 
         $data = array(
             'about' => $about,
              );
            
                    $res    = Response::send('true', 
                                           $data, 
                                           $message ='Success', 
                                        $code = 200);
                    return $res;
            
    }  
    
    public function indexTerms()
    {
    $term = Term::select('terms.*')->get(); 
         $data = array(
             'term' => $term,
              );
            
                    $res    = Response::send('true', 
                                           $data, 
                                           $message ='Success', 
                                        $code = 200);
                    return $res;
            
    }  
 public function indexPolicy()
    {
    $policy = PrivacyPolicy::select('privacy_policy.*')->get(); 
         $data = array(
             'policy' => $policy,
              );
            
                    $res    = Response::send('true', 
                                           $data, 
                                           $message ='Success', 
                                        $code = 200);
                    return $res;
            
    }  
}
