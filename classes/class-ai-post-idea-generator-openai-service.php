<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ai_post_idea_generator_openai_service
 *
 * This class provides functions related to OpenAI.
 * It ensures that the class is only defined once by checking if it already exists.
 *
 * @package AI_Post_Idea_Generator
 */
if ( ! class_exists( 'ai_post_idea_generator_openai_service' ) ) {
	class ai_post_idea_generator_openai_service {


		/**
		 * @var \OpenAI\Client $client The OpenAI client instance used for interacting with the OpenAI API.
		 */
		protected $client;

		/**
		 * @var mixed $helpers An instance of helper classes or utilities used by the AI Post Idea Generator service.
		 */
		public $helpers;

		/**
		 * AI_Post_Idea_Generator_OpenAI_Service constructor.
		 *
		 * Initializes the OpenAI service client using the API key from the settings.
		 * The API key is decrypted before being used to create the client.
		 *
		 * @return void
		 */
		public function __construct() {
			$this->helpers = ai_post_idea_generator_helpers::getInstance();
			$api_key       = $this->helpers->getSetting( 'openai_api_key', '' );
			error_log( 'service: ' . $api_key );
			$this->client = OpenAI::client( $api_key );
		}

		/**
		 * Retrieves a list of assistants from the OpenAI service.
		 *
		 * @return array An associative array containing the success status and either the list of assistants or an error message.
		 *               - 'success' (bool): Indicates whether the request was successful.
		 *               - 'data' (array): The list of assistants if the request was successful.
		 *               - 'message' (string): The error message if the request failed.
		 */
		public function getAssistants() {
			try {
				$response = $this->client->assistants()->list(
					array(
						'limit' => 10,
					)
				);
				$response = $response->toArray();
				return array(
					'success' => true,
					'data'    => $response['data'],
				);
			} catch ( Exception $e ) {
				return array(
					'success' => false,
					'message' => $e->getMessage(),
				);
			}
		}

		/**
		 * Creates an assistant using the OpenAI service.
		 *
		 * @param string $name The name of the assistant to be created.
		 * @return array An associative array containing the success status and either the assistant data or an error message.
		 *               - 'success' (bool): Indicates whether the assistant creation was successful.
		 *               - 'data' (array): Contains the assistant data if creation was successful.
		 *               - 'message' (string): Contains the error message if creation failed.
		 */
		public function createAssistant( $name ) {
			try {
				$response = $this->client->assistants()->create(
					array(
						'name'        => $name,
						'model'       => 'gpt-4o-mini',
						'description' => __( 'Assistant for generating post ideas.', 'ai-post-idea-generator' ),
						'temperature' => 0.5,
					)
				);
				$response = $response->toArray();
				return array(
					'success' => true,
					'data'    => $response,
				);
			} catch ( Exception $e ) {
				return array(
					'success' => false,
					'message' => $e->getMessage(),
				);
			}
		}

		/**
		 * Retrieves the assistant ID based on the current site URL.
		 *
		 * This function first converts the site URL to an uppercase string with underscores
		 * instead of dots. It then checks if an assistant with this name already exists.
		 * If it does, the assistant ID is updated in the settings and returned.
		 * If not, a new assistant is created, its ID is updated in the settings, and returned.
		 *
		 * @return string|null The assistant ID if found or created, otherwise null.
		 */
		public function getAssistantId() {
			$name       = get_site_url();
			$url        = parse_url( $name );
			$name       = strtoupper( str_replace( '.', '_', $url['host'] ) );
			$assistants = $this->getAssistants();
			if ( isset( $assistants['data'] ) ) {
				foreach ( $assistants['data'] as $assistant ) {
					if ( $assistant['name'] === $name ) {
						$this->helpers->updateSetting( 'openai_assistant_id', $assistant['id'] );
						return $assistant['id'];
					}
				}
			}
			$assistant = $this->createAssistant( $name );
			if ( $assistant['success'] ) {
				$assistant_id = $assistant['data']['id'];
				$this->helpers->updateSetting( 'openai_assistant_id', $assistant_id );
				return $assistant_id;
			}
		}

		/**
		 * Retrieves an existing OpenAI thread or creates a new one if it doesn't exist.
		 *
		 * This method first attempts to retrieve the OpenAI thread from the settings.
		 * If the thread does not exist, it will create a new thread using the OpenAI assistant ID.
		 * The new thread is then saved in the settings and returned.
		 *
		 * @return array The OpenAI thread settings.
		 */
		public function getOrCreateThread() {
			$thread = $this->helpers->getSetting( 'openai_thread', false );
			if ( $thread ) {
				return $thread;
			}
			$assistant_id = $this->helpers->getSetting( 'openai_assistant_id', false );
			if ( ! $assistant_id ) {
				$assistant_id = $this->getAssistantId();
			}
			try {
				$response = $this->client->threads()->create(
					array(
						'metadata' => array(
							'assistant_id' => (string) $assistant_id,
							'tool'         => 'ai-post-idea-generator',
						),
					)
				);
				$response = $response->toArray();
				$settings = $this->helpers->updateSetting( 'openai_thread', $response );
				return array(
					'success' => true,
					'data'    => $settings['openai_thread'],
				);
			} catch ( Exception $e ) {
				return array(
					'success' => false,
					'message' => $e->getMessage(),
				);
			}
		}

		public function processPrompt( $prompt ) {
			$thread              = $this->getOrCreateThread();
			$openai_assistant_id = $this->getAssistantId();
			try {
				$run_meta     = array(
					'assistant_id'        => $openai_assistant_id,
					'temperature'         => 0.2,
					'top_p'               => 0.1,
					'truncation_strategy' => array(
						'type'          => 'last_messages',
						'last_messages' => 1,
					),
					'instructions'        => $prompt,
				);
				$run_response = $this->client->threads()->runs()->create( $thread['id'], $run_meta );
				$run_response = $run_response->toArray();
				$status       = $run_response['status'];

				while ( $status !== 'completed' ) {
					$run_response = $this->client->threads()->runs()->retrieve( $run_response['thread_id'], $run_response['id'] );
					$run_response = $run_response->toArray();
					$status       = $run_response['status'];
					if ( $status === 'queued' ) {
					}
					if ( $status === 'in_progress' ) {
					}
					if ( $status === 'cancelling' ) {
					}
					if ( $status === 'cancelled' ) {
					}
					if ( $status === 'failed' ) {
					}
					if ( $status === 'incomplete' ) {
					}
					if ( $status === 'expired' ) {
					}
					if ( $status === 'requires_action' ) {
					}
					if ( $status === 'completed' ) {
						$messages = $this->client->threads()->messages()->list(
							$run_response['thread_id'],
							array(
								'limit' => 1,
								'order' => 'desc',
							)
						);
						$messages = $messages->toArray();
						foreach ( $messages['data'] as $message ) {
							if ( $message['role'] == 'assistant' ) {
								$this->helpers->updateSetting( 'openai_post_ideas', $message );
								return array(
									'success' => true,
									'data'    => $message,
								);
							}
						}
					}
				}
			} catch ( Exception $e ) {
				return array(
					'success' => false,
					'message' => $e->getMessage(),
				);
			}
			return $thread;
		}
	}

}
