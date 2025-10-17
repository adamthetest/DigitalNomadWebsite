<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

/**
 * City Model
 *
 * Represents a city in the digital nomad platform with location data,
 * cost of living information, and related amenities.
 *
 * @property int $id
 * @property int $country_id
 * @property string $name
 * @property string $slug
 * @property float $latitude
 * @property float $longitude
 * @property string|null $description
 * @property string|null $overview
 * @property int|null $population
 * @property string|null $climate
 * @property int|null $internet_speed_mbps
 * @property int|null $safety_score
 * @property float|null $cost_of_living_index
 * @property string|null $best_time_to_visit
 * @property array|null $highlights
 * @property array|null $images
 * @property bool $is_featured
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Country $country
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Neighborhood> $neighborhoods
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CoworkingSpace> $coworkingSpaces
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CostItem> $costItems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Article> $articles
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Neighborhood> $activeNeighborhoods
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CoworkingSpace> $activeCoworkingSpaces
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Article> $publishedArticles
 *
 * @method static \Illuminate\Database\Eloquent\Builder|City newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|City newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|City query()
 * @method static \Illuminate\Database\Eloquent\Builder|City whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereOverview($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City wherePopulation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereClimate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereInternetSpeedMbps($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereSafetyScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereCostOfLivingIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereBestTimeToVisit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereHighlights($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereUpdatedAt($value)
 */
class City extends Model
{
    /** @use HasFactory<\Database\Factories\CityFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'country_id',
        'name',
        'slug',
        'latitude',
        'longitude',
        'description',
        'overview',
        'population',
        'climate',
        'internet_speed_mbps',
        'safety_score',
        'cost_of_living_index',
        'best_time_to_visit',
        'highlights',
        'images',
        'is_featured',
        'is_active',
        // AI-ready fields
        'cost_accommodation_monthly',
        'cost_food_monthly',
        'cost_transport_monthly',
        'cost_coworking_monthly',
        'cost_currency',
        'internet_reliability_score',
        'fiber_available',
        'mobile_data_good',
        'weather_data',
        'avg_temperature_celsius',
        'avg_humidity_percent',
        'rainy_days_per_year',
        'safety_details',
        'female_safe',
        'lgbtq_friendly',
        'visa_options',
        'visa_duration_days',
        'visa_extensions_possible',
        'visa_cost_usd',
        'coworking_spaces_count',
        'cafes_with_wifi_count',
        'english_widely_spoken',
        'nomad_communities',
        'ai_summary',
        'ai_tags',
        'ai_data_updated_at',
        'data_source',
        'last_data_sync',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'cost_of_living_index' => 'decimal:2',
        'highlights' => 'array',
        'images' => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        // AI-ready field casts
        'cost_accommodation_monthly' => 'decimal:2',
        'cost_food_monthly' => 'decimal:2',
        'cost_transport_monthly' => 'decimal:2',
        'cost_coworking_monthly' => 'decimal:2',
        'fiber_available' => 'boolean',
        'mobile_data_good' => 'boolean',
        'weather_data' => 'array',
        'safety_details' => 'array',
        'female_safe' => 'boolean',
        'lgbtq_friendly' => 'boolean',
        'visa_options' => 'array',
        'visa_extensions_possible' => 'boolean',
        'visa_cost_usd' => 'decimal:2',
        'english_widely_spoken' => 'boolean',
        'nomad_communities' => 'array',
        'ai_summary' => 'array',
        'ai_tags' => 'array',
        'ai_data_updated_at' => 'datetime',
        'last_data_sync' => 'datetime',
    ];

    /**
     * Boot the model and set up event listeners.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($city) {
            if (empty($city->slug)) {
                $city->slug = Str::slug($city->name);
            }
        });
    }

    /**
     * Get the country that this city belongs to.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get all neighborhoods in this city.
     */
    public function neighborhoods(): HasMany
    {
        return $this->hasMany(Neighborhood::class);
    }

    /**
     * Get all coworking spaces in this city.
     */
    public function coworkingSpaces(): HasMany
    {
        return $this->hasMany(CoworkingSpace::class);
    }

    /**
     * Get all cost items for this city.
     */
    public function costItems(): HasMany
    {
        return $this->hasMany(CostItem::class);
    }

    /**
     * Get all articles about this city.
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    /**
     * Get all jobs in this city.
     */
    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }

    /**
     * Get only active neighborhoods in this city.
     */
    public function activeNeighborhoods(): HasMany
    {
        return $this->hasMany(Neighborhood::class)->where('is_active', true);
    }

    /**
     * Get only active coworking spaces in this city.
     */
    public function activeCoworkingSpaces(): HasMany
    {
        return $this->hasMany(CoworkingSpace::class)->where('is_active', true);
    }

    /**
     * Get only published articles about this city.
     */
    public function publishedArticles(): HasMany
    {
        return $this->hasMany(Article::class)->where('status', 'published');
    }

    /**
     * Get AI contexts for this city.
     */
    public function aiContexts(): MorphMany
    {
        return $this->morphMany(AiContext::class, 'context', 'context_model', 'context_id');
    }
}
