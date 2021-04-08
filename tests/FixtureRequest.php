<?php

declare(strict_types=1);

namespace Sajya\Server\Tests;

use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Http\FormRequest;
use Sajya\Server\Binding\HandlesRequestParameters;
use Sajya\Server\Binding\BindsParameters;

class FixtureRequest extends FormRequest implements BindsParameters
{
    use HandlesRequestParameters;
    
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user' => 'bail|required|max:255',
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function getBindings(): array
    {
        return [
            'userById'    => 'user',
            'userByEmail' => 'user:email',
            'wrongTypeVar'=> 'user',
            'userNestedId'=> ['user','id']
        ];
    }
    
    /**
     * @inheritDoc
     *
     * @return null|false|\Illuminate\Database\Eloquent\Model
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function resolveParameter(string $parameterName)
    {
        if ('userCustom' === $parameterName) {
            $user = app()->make(User::class);
            return $user->resolveRouteBinding($this->input('user'));
        }
        if ('customer' === $parameterName) {
            $user = app()->make(User::class);
            return $user->resolveRouteBinding(static::resolveRequestValue($this->request->all(), ['user','id']));
        }
        return false;
    }
}
