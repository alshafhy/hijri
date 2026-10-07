@extends('layouts/contentLayoutMaster')

@section('title', __('Contact messages'))

@section('content')
<section id="contact-messages">
  <div class="card">
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead class="table-light"><tr><th>{{ __('Name') }}</th><th>{{ __('Company') }}</th><th>{{ __('Email') }}</th><th>{{ __('Phone') }}</th><th>{{ __('Message') }}</th><th>{{ __('Created at') }}</th><th></th></tr></thead>
        <tbody>
          @forelse ($messages as $message)
            <tr @class(['fw-bolder' => ! $message->read_at])>
              <td>{{ $message->name }}</td>
              <td>{{ $message->company }}</td>
              <td><a href="mailto:{{ $message->email }}">{{ $message->email }}</a></td>
              <td dir="ltr">{{ $message->phone_number }}</td>
              <td style="white-space: pre-line; max-width: 380px;">{{ $message->message }}</td>
              <td class="text-nowrap">{{ $message->created_at?->format('Y/m/d H:i') }}</td>
              <td>
                <form method="POST" action="{{ route('dashboard.contact-messages.read', $message) }}">@csrf @method('PUT')
                  <button class="btn btn-sm btn-outline-secondary">{{ $message->read_at ? __('Mark unread') : __('Mark read') }}</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted">{{ __('No messages yet.') }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if ($messages->hasPages())
      <div class="card-footer">{{ $messages->links() }}</div>
    @endif
  </div>
</section>
@endsection
