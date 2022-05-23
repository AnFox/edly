<?php

namespace App\Http\Requests\Admin;

use App\Contracts\Repositories\ChatRepository;
use App\Contracts\Repositories\UserRepository;
use Illuminate\Foundation\Http\FormRequest;

class DeleteChatMessageRequest extends FormRequest
{
    /**
     * @var ChatRepository
     */
    private $chatRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * DeleteChatMessageRequest constructor.
     * @param ChatRepository $chatRepository
     * @param UserRepository $userRepository
     */
    public function __construct(ChatRepository $chatRepository, UserRepository $userRepository)
    {
        $this->chatRepository = $chatRepository;
        $this->userRepository = $userRepository;
    }
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $chat = $this->chatRepository->find($this->route('chat'))->getModel();
        $webinarOwner = $chat->webinar->getOwner();
        $this->userRepository->setModel($webinarOwner);
        $account = $this->userRepository->getFirstLinkedAccount();

        $user = request()->user();
        $this->userRepository->setModel($user);
        $accounts = $this->userRepository->getLinkedAccounts();

        return $accounts->contains($account->id);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'idList' => 'required|array',
        ];
    }
}
