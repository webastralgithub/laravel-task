<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>
    @if(session()->has('message'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold"> {{ session()->get('message') }}</strong>
            <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
    <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
  </span>
        </div>

    @endif
    <div style="width: 50%;margin: auto" class="py-12 w-6/12">

        <!-- Validation Errors -->
        <x-auth-validation-errors class="mb-4" :errors="$errors" />

        <form method="POST" action="{{ route('update',$user->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <!-- Name -->
            <div>
                <x-label for="name" :value="__('Name')" />

                <x-input id="name" class="block mt-1 w-full" type="text" name="name" value="{{$user->name}}" required autofocus />
            </div>

            <!-- Email Address -->
            <div class="mt-4">
                <x-label for="email" :value="__('Email')" />

                <x-input id="email" class="block mt-1 w-full" type="email" name="email" value="{{$user->email}}"  />
            </div>
            <!-- Phone -->
            <div>
                <x-label for="name" :value="__('Phone No')" />

                <x-input id="phone" class="block mt-1 w-full" type="text" name="phone" value="{{$user->phone}}" required autofocus />
            </div>

            <!-- Photo -->
            <div>
                <x-label for="name" :value="__('Photo')" />

                <x-input id="photo" class="block mt-1 w-full" type="file" name="photo"  />

            </div>

            <img height="150" width="150" src="{{asset("/storage/User/".$user->photo)}}">
            <!-- Password -->


            <div class="flex items-center justify-end mt-4">


                <x-button class="ml-4">
                    {{ __('Update') }}
                </x-button>
            </div>
        </form>
    </div>
</x-app-layout>

