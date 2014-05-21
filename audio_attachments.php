<?php

/**
* Plays audio attachments inside the message window unsing the <audio>-tag.
*
* @license GNU GPLv3+
* @author Ole Schütt
*/

class audio_attachments extends rcube_plugin
{
	public $task = 'mail';

	private $message;

	function init(){
		$rcmail = rcmail::get_instance();
		if ($rcmail->action == 'show' || $rcmail->action == 'preview') {
			$this->add_hook('message_load', array($this, 'message_load'));
			$this->add_hook('template_object_messagebody', array($this, 'html_output'));
		}
	}

	/**
	* Stores a reference to the message object
	*/
	function message_load($p){
		$this->message = $p['object'];
	}

	/**
	* This callback function adds a <audio> tag for each audio attachment
	* @see http://www.w3schools.com/html/html_sounds.asp
	*/
	function html_output($p){
		foreach ((array)$this->message->attachments as $attachment){
			$mimetype = $attachment->mimetype;
			if(preg_match('/^application\/octet-stream/', $mimetype)){
				/* If we have no useful MIME type, then try to detect it. */
				$contents = $this->message->get_part_content($attachment->mime_id, null, true);
				$mimetype = rcube_mime::file_content_type($contents, $attachment->filename, $mimetype, true, true);
			}
			if(!preg_match('/^audio\//', $mimetype))
				continue;

			$url = $this->message->get_part_url($attachment->mime_id);

			$html  = "\n".'<hr><div style="text-align:center">';
			$html .= '<h4>'.$attachment->filename.'</h4>';
			$html .= '<audio controls="controls"><source src="';
			$html .= $url;
			$html .= '" type="';
			$html .= $mimetype;
			$html .= '" />';
			$html .= '<embed height="50px" width="100px" src="';
			$html .= $url;
			$html .= '" />';
			$html .= '</audio></div>';

			$p['content'] .= $html;
		}
		return $p;
	}

}
