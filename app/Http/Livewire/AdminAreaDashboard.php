<?php

namespace App\Http\Livewire;

use App\Models\Category;
use App\Models\Post;
use App\Models\PostReply;
use App\Models\User;
use Asantibanez\LivewireCharts\Models\ColumnChartModel;
use Asantibanez\LivewireCharts\Models\LineChartModel;
use Asantibanez\LivewireCharts\Models\PieChartModel;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AdminAreaDashboard extends Component
{
    private $numberOfEntitiesOverallChart;
    private $postsCreatedByDateChart;
    private $topFiveUsersPostsChart;
    private $topFiveUsersRepliesChart;
    private $lastSixMonthChart;
    private $monthChart;

    public function mount()
    {
        $this->prepareNumberOfEntitiesOverallChart();
        $this->prepareNumberOfEntitiesCurrentMonthChart();
        $this->preparePostsGroupedByCreationDateChart();
        $this->prepareTopFiveUsersPostsChart();
        $this->prepareTopFiveUsersRepliesChart();
        $this->prepareLastSixMonthChart();
    }

    public function render()
    {
        return view('livewire.admin-area-dashboard', [
            'numberOfEntitiesChart' => $this->numberOfEntitiesOverallChart,
            'postsCreatedByDateChart' => $this->postsCreatedByDateChart,
            'topFiveUsersPostsChart' => $this->topFiveUsersPostsChart,
            'topFiveUsersRepliesChart' => $this->topFiveUsersRepliesChart,
            'lastSixMonthChart' => $this->lastSixMonthChart,
            'monthChart' => $this->monthChart,
        ]);
    }

    private function prepareNumberOfEntitiesOverallChart()
    {
        $this->numberOfEntitiesOverallChart =
            (new ColumnChartModel())
            ->setTitle(__('Number of entities'))
            ->addColumn(__('Users'), User::count(), '#90cdf4')
            ->addColumn(__('Categories'), Category::count(), '#f6ad55')
            ->addColumn(__('Posts'), Post::count(), '#fc8181')
            ->addColumn(__('Replies'), PostReply::count(), '#62de76')
            ->withoutLegend()
            ->setDataLabelsEnabled(true);
    }

    private function prepareNumberOfEntitiesCurrentMonthChart()
    {
        $usersCreatedInCurrentMonth = User::whereYear('created_at', Carbon::now()->year)->whereMonth('created_at', Carbon::now()->month)->count();

        $categoriesCreatedInCurrentMonth = Category::whereYear('created_at', Carbon::now()->year)->whereMonth('created_at', Carbon::now()->month)->count();

        $postsCreatedInCurrentMonth = Post::whereYear('created_at', Carbon::now()->year)->whereMonth('created_at', Carbon::now()->month)->count();

        $postRepliesCreatedInCurrentMonth = PostReply::whereYear('created_at', Carbon::now()->year)->whereMonth('created_at', Carbon::now()->month)->count();

        $this->monthChart =
            (new ColumnChartModel())
            ->setTitle('Entities created this month')
            ->addColumn('Users', $usersCreatedInCurrentMonth, '#90cdf4')
            ->addColumn('Categories', $categoriesCreatedInCurrentMonth, '#f6ad55')
            ->addColumn('Posts', $postsCreatedInCurrentMonth, '#fc8181')
            ->addColumn('Replies', $postRepliesCreatedInCurrentMonth, '#62de76')
            ->withoutLegend()
            ->setDataLabelsEnabled(true);
    }

    private function preparePostsGroupedByCreationDateChart()
    {
        $dateThreeMonthsAgo = Carbon::now()->subMonths(3);

        $postsGroupedCounted = Post::where('created_at', '>=', Carbon::now()->subMonths(3))
            ->groupBy('created_at')
            ->select([
                DB::raw('DATE( created_at ) as date'),
                DB::raw('COUNT( * ) as "count"'),
            ])
            ->get()
            ->pluck('count', 'date')->toArray();

        $fromDate = $dateThreeMonthsAgo;
        $toDate = Carbon::now();
        $period = CarbonPeriod::create($fromDate, '1 day', $toDate);

        $resultData = [];
        foreach ($period as $dt) {
            $loopDate = $dt->format('Y-m-d');
            if (array_key_exists($loopDate, $postsGroupedCounted)) {
                $resultData[$loopDate] = $postsGroupedCounted[$loopDate];
            } else {
                $resultData[$loopDate] = 0;
            }
        }

        $this->postsCreatedByDateChart =
            (new LineChartModel())
            ->setTitle(__('Number of created Posts (last three months)'))
            ->setGridVisible(true)
            ->setSmoothCurve();

        foreach ($resultData as $date => $numberOfPosts) {
            $this->postsCreatedByDateChart->addPoint($date, $numberOfPosts);
        }
    }

    private function prepareTopFiveUsersPostsChart()
    {
        $users = User::withCount('posts')->orderBy('posts_count', 'DESC')->limit(5)->get();

        $macro = (new PieChartModel())->setTitle(__('Top 5 Users - Most Posts'))->withoutLegend()
            ->setDataLabelsEnabled(true);

        $colors = ['#90cdf4', '#f6ad55', '#fc8181', '#62de76', '#f1f2de'];

        foreach ($users as $key => $user) {
            $macro->addSlice($user->name, (float) $user->posts_count, $colors[$key]);
        }

        $this->topFiveUsersPostsChart = $macro;
    }

    private function prepareTopFiveUsersRepliesChart()
    {
        $users = User::withCount('postReplies')->orderBy('post_replies_count', 'DESC')->limit(5)->get();

        $macro = (new PieChartModel())->setTitle(__('Top 5 Users - Most Replies'))->withoutLegend()
            ->setDataLabelsEnabled(true);

        $colors = ['#90cdf4', '#f6ad55', '#fc8181', '#62de76', '#f1f2de'];

        foreach ($users as $key =>  $user) {
            $macro->addSlice($user->name, (float) $user->post_replies_count, $colors[$key]);
        }

        $this->topFiveUsersRepliesChart = $macro;
    }

    private function prepareLastSixMonthChart()
    {
        $this->lastSixMonthChart =
            (new LineChartModel())
            ->setTitle(__('Last Six Month'))
            ->multiLine()
            ->withoutLegend()
            ->setDataLabelsEnabled(true);

        $j = 5;
        for ($i = 0; $i <= 5; $i++) {
            $month = Carbon::now()->subMonths($j);

            $postsInMonth = Post::whereDate('created_at', '>=', $month)->count();

            $postRepliesInMonth = PostReply::whereDate('created_at', '>=', $month)->count();

            $usersInMonth = User::whereDate('created_at', '>=', $month)->count();

            $this->lastSixMonthChart->addSeriesPoint(__('Posts'), date('M', mktime(null, null, null, $i)), $postsInMonth);
            $this->lastSixMonthChart->addSeriesPoint(__('Replies'), date('M', mktime(null, null, null, $i)), $postRepliesInMonth);
            $this->lastSixMonthChart->addSeriesPoint(__('User Registrations'), date('M', mktime(null, null, null, $i)), $usersInMonth);

            $j--;
        }
    }
}
