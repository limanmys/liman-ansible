<div class="row">
    <table class="table">
        @foreach ($data as $item)
            <tr><th> {{ \Illuminate\Support\Str::title(str_replace('_', ' ', trim($item['name']))) }} : </th> <td> {{$item["value"]}}  </td></tr>
        @endforeach
    </table>
</div>