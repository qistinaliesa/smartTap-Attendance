
@extends('master.layout')

@section('content')
<div class="container mt-4">
    <h1>Registered Student</h1>
    <p>List of Registered Student</p>

    <div class="row mt-4">
        <div class="col-md-12">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="font-weight-bold">No</th>
                        <th class="font-weight-bold">Matric ID</th>
                        <th class="font-weight-bold">Name</th>
                        <th class="font-weight-bold">Card UID</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cards as $index => $card)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $card->matric_id }}</td>
                            <td>{{ $card->name }}</td>
                            <td>{{ $card->uid }}</td>


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
@endsection

