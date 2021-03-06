@extends('layouts.app')

@section('content')

        <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ __('Home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('category.show', $post->category->id) }}">{{ $post->category->name}}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $post->title }}</li>
        </ol>
        </nav>

        <div class="card">
            <div class="card-body">
                <h1 class="card-title">{{ $post->title }}</h1>
                <div class="card-subtitle mb-2 text-muted">
                    <span class="text-muted">
                        {{ __('by') }}
                        @if ($post->user->trashed()) <i>{{ __('a deleted user') }}:</i>
                        @else <strong><a href=" {{ route('users.show', $post->user) }}">{{$post->user->username}}</a></strong>
                        @endif
                    </span>

                    <span>{{ $post->created_at->diffForHumans()}}</span>

                    <div class="text-right">
                        @can ('update post')
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#editPostModal">
                                {{__('Edit') }}
                            </button>
                        @endcan

                        @can ('delete post')
                            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deletePostModal">
                            {{__('Delete') }}
                            </button>
                        @endcan
                    </div>
                </div>

                <div class="card-text">
                    @markdown($post->content)
                    {{-- begin show all uploads to this post --}}
                    <div>
                        <p>{{ __('Images') }}:</p>
                        @forelse ($images as $image)
                            <img src="{{ asset('storage/'.$image->filename) }}" width="100" alt="">
                        @empty
                            {{ __('No uploded images for this post.') }}
                        @endforelse
                        </p>
                        <ul>{{ __('Files') }}:
                            @forelse ($otherFiles as $otherFile)
                                <li class="list-unstyled"><a href="{{ asset('storage/'.$otherFile->filename) }}">{{ basename($otherFile->filename) }}</a></li>
                            @empty
                                <li class="list-unstyled">{{ __('No uploded Files for this post.') }}</li>
                            @endforelse
                        </ul>

                    </div>
                    {{-- end show all uploads to this post --}}
                </div>
<hr>
                <h1>{{ __('Replies') }}:</h1>
               
                <!-- begin show PostReplies -->
                @can('create post replies')
                <div>
                    <form action="{{ route('replies.store', $post) }}" method="POST">
                        @csrf
                        <div class="form-group p-2">
                            <label for="postContent"> {{ __('Write your reply:') }}</label>
                            <x-easy-mde class="w-100" name="content" :options="['hideIcons' => ['image'], 'minHeight' => '150px']"/>
                        </div>
                        <button type="submit" class="btn ml-2" style="background-color: {{ config('app.settings.primary_color') }}; color: {{ config('app.settings.button_text_color') }};"> {{ __('Create Reply') }}</button>
                    </form>
                </div>
                @else
                <div>
                    <form action="{{ route('replies.store', $post) }}" method="POST">
                        @csrf
                        <div class="form-group p-2">
                            <label for="postContent">{{ __('Write your reply:') }}:</label><br>
                            <textarea class="w-100 disabled-reply" disabled placeholder="{{ __( 'Login to leavea reply') }}"></textarea>
                        </div>
                        <button disabled type="submit" class="btn ml-2" style="background-color: {{ config('app.settings.primary_color') }}; color: {{ config('app.settings.button_text_color') }};">{{ __('Create Reply') }}</button>
                    </form>
                </div>
                @endcan
                <div class="card-text">
                    <ul class="row my-2 p-2 no-gutters">
                        @forelse ($replies as $reply)

                            <li class="list-unstyled border-bottom w-100">
                                @if ($reply->user->trashed())
                                    <i>{{ __('deleted user') }}:</i>
                                @else
                                    <strong><a href=" {{ route('users.show', $reply->user) }}">{{$reply->user->username}}</a></strong>:
                                @endif
                                <small>{{ $reply->created_at->diffForHumans()}}</small>
                                <div class="border-bottom">
                                    @markdown($reply->content)

                                    <div class="text-right">
                                        @can('create post replies')
                                        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#replyModal_{{$reply->id}}">
                                            {{__('Comment') }}
                                        </button>
                                        @endcan

                                        @can ('edit reply')
                                        <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#editReplyModal_{{$reply->id}}">
                                            {{__('Edit') }}
                                        </button>
                                        @endcan

                                        @can ('delete reply')
                                        <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteReplyModal_{{$reply->id}}">
                                            {{__('Delete') }}
                                        </button>
                                        @endcan

                                    </div>
                                </div>

                                <!-- begin update reply modaL -->
                                @can ('edit reply')
                                <div class="modal fade" id="editReplyModal_{{$reply->id}}" tabindex="-1" role="dialog" aria-labelledby="editReplyModalLabel">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 class="modal-title" id="editReplyModalLabel">{{__('Edit Reply') }}</h4>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                                            </div>
                                            <form action="{{ route('replies.update', $reply->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')

                                                <div class="modal-body">
                                                    <x-easy-mde name="content" :options="['hideIcons' => ['image']]">
                                                        {{ $reply->content }}
                                                    </x-easy-mde>
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-danger" data-dismiss="modal">{{__('Close') }}</button>
                                                    <button type="submit" class="btn btn-success">{{__('Update') }}</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endcan
                                <!-- end update reply modaL -->

                                <!-- begin delete reply modaL -->
                                @can ('delete reply')
                                <div class="modal fade" id="deleteReplyModal_{{$reply->id}}" tabindex="-1" role="dialog" aria-labelledby="deleteReplyModalLabel">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 class="modal-title" id="deleteReplyModalLabel">{{ __('Delete this reply') }}?</h4>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>{{$reply->content}}<br><small>{{ __('by') }}: {{$reply->user->username}}</small></p>
                                            </div>
                                            <div class="modal-footer">
                                                <form action="{{ route('replies.destroy', ['postReply' => $reply ]) }}" method="POST">
                                                    @method('DELETE')
                                                    @csrf
                                                    <button type="button" class="btn btn-danger" data-dismiss="modal">{{__('Close') }}</button>
                                                    <button type="submit" class="btn btn-success">{{__('Delete') }}</button>
                                                </form>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endcan
                                <!-- end delete reply modaL -->

                                {{-- show reply to reply --}}
                            
                                <x-replies :reply="$reply" :post="$post" />

                                <!-- Begin post reply Modal -->
                                @can('create post replies')
                                <div class="modal fade" id="replyModal_{{$reply->id}}" tabindex="-1" role="dialog" aria-labelledby="replyModalLabel">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 class="modal-title" id="replyModalLabel">{{__('Leave a comment') }}</h4>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                                            </div>

                                            <form action="{{ route('replies.store', [$post, $reply]) }}" method="POST">
                                            <div class="modal-body">
                                                @csrf
                                                <x-easy-mde name="content" :options="['hideIcons' => ['image']]">{{ old('content') }}</x-easy-mde>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-danger" data-dismiss="modal">{{__('Close') }}</button>
                                                <button type="submit" class="btn btn-success">{{__('Save') }}</button>
                                            </div>
                                            </form>

                                        </div>
                                    </div>
                                </div>
                                @endcan
                                <!-- End post reply Modal -->

                            </li>
                        @empty
                            <li class="row border my-2 p-2 no-gutters">{{ __('No replies found for this post.') }}</li>
                        @endforelse
                    </ul>
                    {{ $replies->links() }}
                </div>
                <!-- end show PostReplies -->



            </div>
        </div>

<!-- Begin Delete Post Modal -->
@can('delete posts')
<div class="modal fade" id="deletePostModal" tabindex="-1" role="dialog" aria-labelledby="deletePostModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="deletePostModalLabel">{{__('Delete this post') }}?</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <p>{{$post->title}}<br><small>{{ __('by') }}: {{$post->user->name}} </small> </p>
                <p>{{ __('There are') }} <strong>{{count($replies)}}</strong> {{ __('replies to this post. Deleting this post will also delete all replies, uploads and comments associated with it.') }}</p>
            </div>
            <div class="modal-footer">
                <form action="{{ route('posts.destroy', $post) }}" method="POST">
                    @method('DELETE')
                    @csrf
                    <button type="button" class="btn btn-danger" data-dismiss="modal">{{__('Close') }}</button>
                    <button type="submit" class="btn btn-success">{{__('Delete') }}</button>
                </form>
            </div>

        </div>
    </div>
</div>
@endcan
<!-- End Delete Post Modal -->

<!-- Update Post Modal -->
@can ('edit posts')
<div class="modal fade" id="editPostModal" tabindex="-1" role="dialog" aria-labelledby="editPostModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="editPostModalLabel">{{ __('Edit Post') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <form action="{{ route('posts.update', $post->id) }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="modal-body">
                    <div class="form-group p-2">
                        <label for="postTitle">{{ __('Title') }}</label>
                        <input wire:model="title" type="text" value="{{ old('title', $post->title) }}" class="form-control" id="postTitle" name="title" placeholder="Post title" >
                      </div>
                    <x-easy-mde name="content" :options="['hideIcons' => ['image']]">

                        {{ old('content', $post->content) }}
                    </x-easy-mde>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">{{__('Close') }}</button>
                    <button type="submit" class="btn btn-success">{{__('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
<!-- End Update Post Modal -->

@endsection
