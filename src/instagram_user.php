<?php
    
    namespace instagram;
    
    class instagram_user extends instagram_request{
        
        public $username;
        public $password;
        public $functions;
        
        function __construct($username, $password, $functions = null){
            $this->username  = $username;
            $this->password  = $password;
            $this->functions = $functions;
        }
        
        public function get_user_id($username = null){
            
            $username = $username??$this->username;
            if($username != null){
                
                $url = 'https://www.instagram.com/web/search/topsearch/?query='.$username;
                
                $json = $this->request($url);
                $json = json_decode($json['body']);
                
                $user_id = 0;
                foreach($json->users as $user){
                    if($username == $user->user->username){
                        $user_id = $user->user->pk;
                    }
                }
                
                return $user_id;
            }
            
            return false;
            
        }
        
        public function get_user_posts($username = null){
            
            $cache = $this->cache($username.'-posts');
            if($cache == false){
                
                $post_hashquery = $this->get_post_queryhash();
                $user_id        = $this->get_user_id($username);
                $url            = 'https://www.instagram.com/graphql/query/?query_hash='.$post_hashquery.'&variables={"id":"'.$user_id.'","first":50}';
                $json           = $this->request($url);
                $json           = json_decode($json['body'])->data->user;
                
                $this->cache($username.'-posts', $json);
                
                $result = $json;
            }
            else{
                $result = $cache;
            }
            
            return $result;
        }
        
        public function change_profil_pic($image_path = null){
            
            $upload_id         = $this->functions->upload->get_upload_id();
            $upload_session_id = $this->functions->upload->get_upload_session_id($upload_id);
            $url               = 'https://i.instagram.com/rupload_igphoto/'.$upload_session_id;
            
            $file      = file_get_contents($image_path);
            $file_size = strlen($file);
            
            $header = [
                "Content-Type"               => "application/octet-stream",
                "X-Entity-Type"              => "image/jpeg",
                "X-Entity-Name"              => $upload_session_id,
                "Offset"                     => "0",
                "X-Entity-Length"            => $file_size,
                "Cookie"                     => $this->create_cookie(),
                "X-Instagram-Rupload-Params" => $this->functions->upload->rupload_params($upload_id),
            ];
            
            $json = $this->request($url, 'UPLOAD', ['body' => $file], $header);
            $json = json_decode($json['body']);
            if($json->status == 'ok'){
                $result = $this->_change_profil_pic($upload_id);
                if($result->status == 'ok'){
                    return true;
                }
            }
            
            return false;
        }
        
        protected function _change_profil_pic($upload_id){
            
            $url       = 'https://i.instagram.com/api/v1/accounts/change_profile_picture/';
            $post_data = [
                '_csrftoken'     => $this->get_csrftoken(),
                '_uuid'          => $this->get_guid(),
                'use_fbuploader' => 'true',
                'upload_id'      => $upload_id,
            ];
            $json      = $this->request($url, 'POST', $post_data);
            return json_decode($json['body']);
            
        }
        
        public function get_user_info_by_id($user_id = null){
            
            if($user_id != null){
                
                $url = 'https://i.instagram.com/api/v1/users/'.$user_id.'/full_detail_info/';
                
                $json = $this->request($url);
                $json = json_decode($json['body']);
                
                return $json;
            }
            
            return false;
            
        }
        
        public function get_user_info_by_username($username = null){
            
            if($username != null){
                
                $url = 'https://i.instagram.com/api/v1/users/'.$username.'/full_detail_info/';
                
                $json = $this->request($url);
                $json = json_decode($json['body']);
                
                return $json;
            }
            
            return false;
            
        }
        
        public function like($shortcode){
            
            if($shortcode != null){
                
                $url = 'https://i.instagram.com/api/v1/media/'.$shortcode.'/like/';
                
                $post_data = [
                    'container_module' => 'feed_contextual_profile',
                    'delivery_class'   => 'organic',
                    'radio_type'       => 'wifi-none',
                    'feed_position'    => '0',
                    'media_id'         => $shortcode,
                    '_csrftoken'       => $this->get_csrftoken(),
                    '_uuid'            => $this->get_guid(),
                ];
                $post_data = ['signed_body' => 'SIGNATURE.'.json_encode($post_data)];
                
                $json = $this->request($url, 'POST', $post_data);
                $json = json_decode($json['body']);
                
                return $json;
            }
            
            return false;
            
        }
        
        public function unlike($shortcode){
            
            if($shortcode != null){
                
                $url = 'https://i.instagram.com/api/v1/media/'.$shortcode.'/unlike/';
                
                $post_data = [
                    'container_module' => 'feed_contextual_profile',
                    'delivery_class'   => 'organic',
                    'radio_type'       => 'wifi-none',
                    'feed_position'    => '0',
                    'media_id'         => $shortcode,
                    '_csrftoken'       => $this->get_csrftoken(),
                    '_uuid'            => $this->get_guid(),
                ];
                $post_data = ['signed_body' => 'SIGNATURE.'.json_encode($post_data)];
                
                $json = $this->request($url, 'POST', $post_data);
                $json = json_decode($json['body']);
                
                return $json;
            }
            
            return false;
            
        }
        
        public function follow($username){
            
            if($username != null){
                $user_id    = $this->get_user_id($username);
                $me_user_id = $this->get_user_id();
                
                $url = 'https://i.instagram.com/api/v1/friendships/create/'.$user_id.'/';
                
                $post_data = [
                    'container_module' => 'self_following',
                    'radio_type'       => 'wifi-none',
                    'user_id'          => $user_id,
                    '_csrftoken'       => $this->get_csrftoken(),
                    '_uid'             => $me_user_id,
                    '_uuid'            => $this->get_guid(),
                ];
                $post_data = ['signed_body' => 'SIGNATURE.'.json_encode($post_data)];
                
                $json = $this->request($url, 'POST', $post_data);
                $json = json_decode($json['body']);
                
                return $json;
            }
            
            return false;
            
        }
        
        public function unfollow($username){
            
            if($username != null){
                $user_id    = $this->get_user_id($username);
                $me_user_id = $this->get_user_id();
                
                $url = 'https://i.instagram.com/api/v1/friendships/destroy/'.$user_id.'/';
                
                $post_data = [
                    'container_module' => 'self_following',
                    'radio_type'       => 'wifi-none',
                    'user_id'          => $user_id,
                    '_csrftoken'       => $this->get_csrftoken(),
                    '_uid'             => $me_user_id,
                    '_uuid'            => $this->get_guid(),
                ];
                $post_data = ['signed_body' => 'SIGNATURE.'.json_encode($post_data)];
                
                $json = $this->request($url, 'POST', $post_data);
                $json = json_decode($json['body']);
                
                return $json;
            }
            
            return false;
            
        }
        
        public function send_inbox_text($username, $text = 'Hello'){
            
            if($username != null){
                
                $user_id = $this->get_user_id($username);
                $url     = 'https://i.instagram.com/api/v1/direct_v2/threads/broadcast/text/';
                //$thread_id = $this->get_inbox_user_thread($username);
                $post_data = [
                    'text'                 => $text,
                    'action'               => 'send_item',
                    'is_shh_mode'          => '0',
                    'recipient_users'      => '[['.$user_id.']]',
                    //'thread_ids'           => '['.$thread_id['thread_id'].']',
                    'send_attribution'     => 'direct_thread',
                    'client_context'       => $this->generate_client_context(),
                    '_csrftoken'           => $this->get_csrftoken(),
                    'device_id'            => $this->get_device_id(),
                    'mutation_token'       => $this->generate_client_context(),
                    '_uuid'                => $this->get_guid(),
                    'offline_threading_id' => $this->generate_client_context(),
                ];
                
                $json = $this->request($url, 'POST', $post_data);
                $json = json_decode($json['body']);
                
                return $json;
            }
            
            return false;
            
        }
        
        public function send_inbox_like($username){
            
            if($username != null){
                
                $user_id = $this->get_user_id($username);
                $url     = 'https://i.instagram.com/api/v1/direct_v2/threads/broadcast/like/';
                //$thread_id = $this->get_inbox_user_thread($username);
                $post_data = [
                    'action'               => 'send_item',
                    'is_shh_mode'          => '0',
                    'recipient_users'      => '[['.$user_id.']]',
                    //'thread_ids'           => '['.$thread_id['thread_id'].']',
                    'send_attribution'     => 'direct_thread',
                    'client_context'       => $this->generate_client_context(),
                    '_csrftoken'           => $this->get_csrftoken(),
                    'device_id'            => $this->get_device_id(),
                    'mutation_token'       => $this->generate_client_context(),
                    '_uuid'                => $this->get_guid(),
                    'offline_threading_id' => $this->generate_client_context(),
                ];
                
                $json = $this->request($url, 'POST', $post_data);
                $json = json_decode($json['body']);
                
                return $json;
            }
            
            return false;
            
        }
        
        public function send_inbox_photo($username, $image_path = null){
            
            if($username != null and $image_path != null){
                
                //IMAGE UPLOAD
                $upload_id         = $this->functions->upload->get_upload_id();
                $upload_session_id = $this->functions->upload->get_upload_session_id($upload_id);
                $url               = 'https://i.instagram.com/rupload_igphoto/'.$upload_session_id;
                
                $file      = file_get_contents($image_path);
                $file_size = strlen($file);
                
                $header = [
                    "Content-Type"               => "application/octet-stream",
                    "X-Entity-Type"              => "image/jpeg",
                    "X-Entity-Name"              => $upload_session_id,
                    "Offset"                     => "0",
                    "X-Entity-Length"            => $file_size,
                    "Cookie"                     => $this->create_cookie(),
                    "X-Instagram-Rupload-Params" => $this->functions->upload->rupload_params($upload_id),
                ];
                
                $upload_json = $this->request($url, 'UPLOAD', ['body' => $file], $header);
                $upload_json = json_decode($upload_json['body']);
                //IMAGE UPLOAD
                
                //IMAGE SEND
                $user_id = $this->get_user_id($username);
                $url     = 'https://i.instagram.com/api/v1/direct_v2/threads/broadcast/configure_photo/';
                //$thread_id = $this->get_inbox_user_thread($username);
                $post_data = [
                    'upload_id'               => $upload_json->upload_id,
                    'allow_full_aspect_ratio' => 'true',
                    'action'                  => 'send_item',
                    'is_shh_mode'             => '0',
                    'recipient_users'         => '[['.$user_id.']]',
                    //'thread_ids'              => '['.$thread_id['thread_id'].']',
                    'send_attribution'        => 'direct_thread',
                    'client_context'          => $this->generate_client_context(),
                    '_csrftoken'              => $this->get_csrftoken(),
                    'device_id'               => $this->get_device_id(),
                    'mutation_token'          => $this->generate_client_context(),
                    '_uuid'                   => $this->get_guid(),
                    'offline_threading_id'    => $this->generate_client_context(),
                ];
                $json      = $this->request($url, 'POST', $post_data);
                $json      = json_decode($json['body']);
                //IMAGE SEND
                
                return $json;
            }
            
            return false;
            
        }
        
        public function get_create_inbox_thread($username){
            
            $user_id = $this->get_user_id($username);
            $url     = 'https://i.instagram.com/api/v1/direct_v2/threads/get_by_participants/?recipient_users=%5B'.$user_id.'%5D&seq_id=1573&limit=20';
            $json    = $this->request($url, 'GET');
            //$json    = json_decode($json['body']);
            return $json;
            
        }
        
        public function get_inbox_threads(){
            
            $url  = 'https://i.instagram.com/api/v1/direct_v2/inbox/?visual_message_return_type=unseen&thread_message_limit=10&persistentBadging=true&limit=20&push_disabled=true&fetch_reason=manual_refresh';
            $json = $this->request($url, 'GET');
            $json = json_decode($json['body']);
            return $json;
            
        }
        
        public function get_inbox_user_thread($username = null, $group = false){
            
            if($username){
                
                $threads_id      = null;
                $user_id         = $this->get_user_id($username);
                $threads_id_list = $this->get_inbox_threads();
                if($threads_id_list->inbox->threads != null){
                    foreach($threads_id_list->inbox->threads as $thread){
                        
                        if($group === true and count($thread->users) > 0){
                            foreach($thread->users as $user){
                                if($user->pk == $user_id){
                                    $threads_id = [
                                        'thread_id'    => $thread->thread_id,
                                        'thread_v2_id' => $thread->thread_v2_id,
                                    ];
                                    break;
                                }
                            }
                        }
                        else{
                            if($thread->users[0]->pk == $user_id){
                                $threads_id = [
                                    'thread_id'    => $thread->thread_id,
                                    'thread_v2_id' => $thread->thread_v2_id,
                                ];
                                break;
                            }
                        }
                        
                    }
                    
                    if($threads_id == null){
                        $thread = $this->get_create_inbox_thread($username);
                        print_r($thread);
                        exit;
                        $threads_id = [
                            'thread_id'    => $thread->thread->thread_id,
                            'thread_v2_id' => $thread->thread->thread_v2_id,
                        ];
                    }
                    
                }
                else{
                    $thread     = $this->get_create_inbox_thread($username);
                    $threads_id = [
                        'thread_id'    => $thread->thread->thread_id,
                        'thread_v2_id' => $thread->thread->thread_v2_id,
                    ];
                }
                
                return $threads_id;
                
            }
            
            return false;
            
        }
        
        
        private function generate_client_context(){
            return (round(microtime(true) * 1000) << 22 | random_int(PHP_INT_MIN, PHP_INT_MAX) & 4194303) & PHP_INT_MAX;
        }
    }