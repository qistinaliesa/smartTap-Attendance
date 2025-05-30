{{-- resources/views/cards/index.blade.php --}}
@extends('master.layout')

@section('content')
<div class="content-wrapper">
    <div class="page-header">
        <h3 class="page-title"> Registered Cards </h3>
        <p class="text-muted">Showing all cards registered in the system</p>
    </div>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Cards List</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Card UID</th>
                                    <th>Name</th>
                                    <th>Matric ID</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($cards as $index => $card)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $card->uid }}</td>
                                        <td>{{ $card->name }}</td>
                                        <td>{{ $card->matric_id }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No cards registered yet</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
