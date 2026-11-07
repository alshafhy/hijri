<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Commercial\OfferEstatePaymentStatus;
use App\Enums\Commercial\OfferEstateStatus;
use App\Enums\Commercial\PartyContactOwnerType;
use App\Enums\UserStatus;
use App\Enums\Valuation\RequestState;
use App\Models\Company;
use App\Models\ContactMessage;
use App\Models\Contract;
use App\Models\Contractor;
use App\Models\GeoCity;
use App\Models\GeoNeighborhood;
use App\Models\ImportCheckpoint;
use App\Models\ImportQuarantine;
use App\Models\ImportRun;
use App\Models\LegacyUserMap;
use App\Models\Offer;
use App\Models\OfferEstate;
use App\Models\Partner;
use App\Models\PartnerContract;
use App\Models\PartyContact;
use App\Models\Property;
use App\Models\PropertyAc;
use App\Models\PropertyAdjustment;
use App\Models\PropertyBorder;
use App\Models\PropertyBuilding;
use App\Models\PropertyComparable;
use App\Models\PropertyComponent;
use App\Models\PropertyFacade;
use App\Models\PropertyFlooring;
use App\Models\PropertyLand;
use App\Models\PropertyLocation;
use App\Models\PropertyPicture;
use App\Models\PropertyQuestion;
use App\Models\PropertyService;
use App\Models\PropertyTotal;
use App\Models\RequestFeeShare;
use App\Models\ServiceContract;
use App\Models\SupportRequest;
use App\Models\User;
use App\Models\ValuationAttachment;
use App\Models\ValuationCustomTableRow;
use App\Models\ValuationRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $company = $this->company();
        $staff = $this->staff();
        [$riyadh, $malqa] = $this->geography();
        [$partner, $contractor, $offer, $estate] = $this->commercial($staff['coordinator']);

        $this->supportRecords($company, $staff, $riyadh, $malqa);
        $this->importHarness($staff['evaluator']);

        $this->landRequest($company, $staff, $riyadh, $malqa, $partner, $contractor, $offer, $estate);
        $this->villaRequest($company, $staff, $riyadh, $malqa);
        $this->apartmentRequest($company, $staff, $riyadh, $malqa);
        $this->customTableRequest($company, $staff, $riyadh, $malqa);
        $this->attachmentsRequest($company, $staff, $riyadh, $malqa);
        $this->approvedRequest($company, $staff, $riyadh, $malqa);
        $this->cancelledRequest($company, $staff, $riyadh, $malqa);
        $this->manualAmountRequest($company, $staff, $riyadh, $malqa);
    }

    private function company(): Company
    {
        return Company::query()->updateOrCreate(
            ['name' => 'شركة مقيم للاختبار'],
            [
                'name_en' => 'Muqayem Demo Company',
                'phone_number' => '0501112233',
                'address' => 'الرياض',
                'company_membership_number' => '1110000001',
                'company_reg_number' => '1010000001',
                'membership_date' => '2017-01-01',
                'accreditation_end_date' => '2027-09-15',
                'default_coordinator_share' => 20,
                'default_evaluator_share' => 50,
                'default_manager_share' => 30,
                'request_sla_hours' => 5,
                'evaluation_sla_hours' => 8,
                'status' => 1,
            ]
        );
    }

    /**
     * @return array{manager: User, coordinator: User, evaluator: User}
     */
    private function staff(): array
    {
        $password = Hash::make((string) config('users.demo.password'));
        $branchId = (int) config('users.default_branch_id', 1);
        $demo = config('users.demo');

        $users = [
            'manager' => $this->staffUser($demo['manager'], $password, $branchId, 'manager', true, '0501000001', '1210000001'),
            'coordinator' => $this->staffUser($demo['coordinator'], $password, $branchId, 'coordinator', false, '0501000002', '1210000002'),
            'evaluator' => $this->staffUser($demo['evaluator'], $password, $branchId, 'evaluator', false, '0501000003', '1210000003'),
        ];

        foreach ($users as $role => $user) {
            LegacyUserMap::query()->updateOrCreate(
                ['legacy_user_id' => 900000 + $user->id],
                [
                    'user_id' => $user->id,
                    'legacy_type' => $role,
                    'email' => $user->email,
                    'name' => $user->name,
                ]
            );
        }

        return $users;
    }

    /**
     * @param  array{name: string, username: string, email: string}  $profile
     */
    private function staffUser(array $profile, string $password, int $branchId, string $role, bool $canApprove, string $phone, string $membership): User
    {
        $user = User::query()->updateOrCreate(
            ['username' => $profile['username']],
            [
                'name' => $profile['name'],
                'email' => $profile['email'],
                'password' => $password,
                'branch_id' => $branchId,
                'status' => UserStatus::Active,
                'phone_number' => $phone,
                'membership_number' => $membership,
                'membership_category' => $role === 'evaluator' ? 'أساسي' : 'منتسب',
                'membership_branch_type' => 'عقار',
                'can_approve_final_request' => $canApprove,
                'email_verified_at' => now(),
            ]
        );

        $user->syncRoles([$role]);

        return $user;
    }

    /**
     * @return array{0: GeoCity, 1: GeoNeighborhood}
     */
    private function geography(): array
    {
        $riyadh = GeoCity::query()->updateOrCreate(
            ['legacy_id' => 900001],
            ['name_ar' => 'الرياض', 'name_en' => 'Riyadh']
        );
        GeoCity::query()->updateOrCreate(
            ['legacy_id' => 900002],
            ['name_ar' => 'جدة', 'name_en' => 'Jeddah']
        );

        $malqa = GeoNeighborhood::query()->updateOrCreate(
            ['legacy_id' => 900101],
            ['city_id' => $riyadh->id, 'name_ar' => 'الملقا', 'name_en' => 'Al Malqa']
        );
        GeoNeighborhood::query()->updateOrCreate(
            ['legacy_id' => 900102],
            ['city_id' => $riyadh->id, 'name_ar' => 'النرجس', 'name_en' => 'Al Narjis']
        );

        return [$riyadh, $malqa];
    }

    /**
     * @param  User  $coordinator
     * @return array{0: Partner, 1: Contractor, 2: Offer, 3: OfferEstate}
     */
    private function commercial(User $coordinator): array
    {
        $partner = Partner::query()->updateOrCreate(
            ['name' => 'بنك الاختبار'],
            [
                'email' => 'partner@example.com',
                'phone_number' => '0502000001',
                'x_axis' => '24.7136',
                'y_axis' => '46.6753',
                'state' => Partner::STATE_ACTIVE,
            ]
        );

        $contractor = Contractor::query()->updateOrCreate(
            ['name' => 'متعاقد الاختبار'],
            [
                'email' => 'contractor@example.com',
                'phone_number' => '0502000002',
                'x_axis' => 24.7136,
                'y_axis' => 46.6753,
                'date_from' => now()->subYear()->toDateString(),
                'date_to' => now()->addYear()->toDateString(),
                'fees' => 1500,
                'state' => Contractor::STATE_ACTIVE,
            ]
        );

        PartyContact::query()->updateOrCreate(
            ['email' => 'partner.contact@example.com'],
            [
                'partner_id' => $partner->id,
                'owner_type' => PartyContactOwnerType::Partner,
                'name' => 'جهة اتصال الشريك',
                'phone_number' => '0502000011',
                'status' => 1,
            ]
        );
        PartyContact::query()->updateOrCreate(
            ['email' => 'contractor.contact@example.com'],
            [
                'contractor_id' => $contractor->id,
                'owner_type' => PartyContactOwnerType::Contractor,
                'name' => 'جهة اتصال المتعاقد',
                'phone_number' => '0502000012',
                'status' => 1,
            ]
        );

        $offer = Offer::query()->updateOrCreate(
            ['number' => 'OFF-DEMO-1'],
            [
                'partner_id' => $partner->id,
                'partner_name' => $partner->name,
                'city' => 'الرياض',
                'offered_at' => now()->subDays(3),
                'state' => Offer::STATE_ACCEPTED,
                'legacy_user_id' => $coordinator->id,
            ]
        );

        $estate = OfferEstate::query()->updateOrCreate(
            ['offer_id' => $offer->id, 'instrument_no' => '310101010101'],
            [
                'estate_kind' => 'سكني',
                'estate_type' => 'أرض',
                'area' => 500,
                'neighborhood' => 'الملقا',
                'fees' => 2500,
                'payment_status' => OfferEstatePaymentStatus::Paid,
                'status' => OfferEstateStatus::Active,
            ]
        );

        return [$partner, $contractor, $offer, $estate];
    }

    /**
     * @param  array{manager: User, coordinator: User, evaluator: User}  $staff
     */
    private function supportRecords(Company $company, array $staff, GeoCity $city, GeoNeighborhood $neighborhood): void
    {
        ServiceContract::query()->updateOrCreate(
            ['number' => 900001],
            [
                'company_id' => $company->id,
                'contract_date' => now()->toDateString(),
                'client_name' => 'عميل العقود',
                'client_id_number' => '1000000001',
                'client_phone' => '0503000001',
                'client_address' => 'الرياض',
                'client_email' => 'client@example.com',
                'purpose' => 'تقييم عقار سكني',
                'city_id' => $city->id,
                'neighborhood_id' => $neighborhood->id,
                'property_kind' => 'سكني',
                'property_type' => 'فيلا',
                'instrument_no' => '310101010199',
                'instrument_date' => now()->subMonth()->toDateString(),
                'contract_value' => 3500,
                'representative_name' => $staff['coordinator']->name,
                'created_by' => $staff['coordinator']->id,
            ]
        );

        ContactMessage::query()->updateOrCreate(
            ['email' => 'inbox@example.com', 'message' => 'رسالة تواصل تجريبية للمنصة.'],
            [
                'name' => 'زائر الاختبار',
                'company' => 'شركة الزائر',
                'phone_number' => '0503000002',
                'ip_address' => '127.0.0.1',
                'read_at' => null,
            ]
        );

        SupportRequest::query()->updateOrCreate(
            ['user_id' => $staff['evaluator']->id, 'title' => 'طلب دعم تجريبي'],
            [
                'body' => 'هذه تذكرة دعم للاختبار.',
                'status' => SupportRequest::STATUS_OPEN,
            ]
        );

        if (Schema::hasTable('attachment_types')) {
            $typeId = DB::table('attachment_types')->where('name', 'صورة للصك')->value('id');
            if ($typeId === null) {
                $typeId = DB::table('attachment_types')->insertGetId([
                    'name' => 'صورة للصك',
                    'ar_name' => 'صورة للصك',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $exists = DB::table('attachments')->where('file_name', 'demo-deed.txt')->exists();
            if (! $exists) {
                $path = 'demo/attachments/demo-deed.txt';
                Storage::disk('local')->put($path, 'demo deed');
                DB::table('attachments')->insert([
                    'attachment_type_id' => $typeId,
                    'attachable_type' => Company::class,
                    'attachable_id' => $company->id,
                    'file_path' => $path,
                    'file_name' => 'demo-deed.txt',
                    'mime_type' => 'text/plain',
                    'file_size' => 9,
                    'notes' => 'مرفق تجريبي',
                    'created_at' => now(),
                    'updated_at' => now(),
                    'created_by' => $staff['coordinator']->id,
                ]);
            }
        }
    }

    private function importHarness(User $evaluator): void
    {
        $run = ImportRun::query()->updateOrCreate(
            ['name' => 'demo-import'],
            [
                'status' => 'completed',
                'started_at' => now()->subHour(),
                'finished_at' => now()->subMinutes(50),
                'meta' => ['source' => 'demo'],
            ]
        );

        ImportCheckpoint::query()->updateOrCreate(
            ['import_run_id' => $run->id, 'importer' => 'DemoImporter'],
            ['last_legacy_id' => 100, 'processed' => 8]
        );

        ImportQuarantine::query()->updateOrCreate(
            ['import_run_id' => $run->id, 'entity_type' => 'valuation_request', 'legacy_id' => 999001],
            [
                'payload' => ['number' => 'LEGACY-BAD'],
                'reason' => 'صف تجريبي للحجر الصحي',
            ]
        );
    }

    /**
     * @param  array{manager: User, coordinator: User, evaluator: User}  $staff
     */
    private function landRequest(Company $company, array $staff, GeoCity $city, GeoNeighborhood $neighborhood, Partner $partner, Contractor $contractor, Offer $offer, OfferEstate $estate): void
    {
        $request = $this->request('DEMO-LAND-1', 91001, RequestState::Waiting, $company, $staff, [
            'started_at' => now()->subDays(2),
        ]);
        $property = $this->property($request, 'أرض فضاء', 'أرض', 'عميل الأرض', 'مالك الأرض');
        $this->location($property, $city, $neighborhood, '24.7740', '46.7386');
        $this->borders($property);
        $this->landDetails($property);
        $this->comparables($property);
        $this->feeShare($request);
        $this->linkCommercial($request, $partner, $contractor, $offer, $estate);
    }

    /**
     * @param  array{manager: User, coordinator: User, evaluator: User}  $staff
     */
    private function villaRequest(Company $company, array $staff, GeoCity $city, GeoNeighborhood $neighborhood): void
    {
        $request = $this->request('DEMO-VILLA-1', 91002, RequestState::UnderEvaluation, $company, $staff, [
            'started_at' => now()->subDays(5),
            'under_evaluation_at' => now()->subDays(1),
        ]);
        $property = $this->property($request, 'سكني', 'فيلا', 'عميل الفيلا', 'مالك الفيلا');
        $this->location($property, $city, $neighborhood, '24.7136', '46.6753');
        $this->borders($property);
        $this->landDetails($property, 450);
        $this->buildingDetails($property);
        $this->costComponents($property);
        $this->totals($property, null);
        $this->feeShare($request);
    }

    /**
     * @param  array{manager: User, coordinator: User, evaluator: User}  $staff
     */
    private function apartmentRequest(Company $company, array $staff, GeoCity $city, GeoNeighborhood $neighborhood): void
    {
        $request = $this->request('DEMO-APT-1', 91003, RequestState::Evaluated, $company, $staff, [
            'started_at' => now()->subDays(8),
            'under_evaluation_at' => now()->subDays(4),
            'evaluated_at' => now()->subDay(),
        ]);
        $property = $this->property($request, 'سكني', 'شقة', 'عميل الشقة', 'مالك الشقة');
        $this->location($property, $city, $neighborhood, '24.7748', '46.6435');
        $this->borders($property);
        $this->landDetails($property, 180);
        $this->buildingDetails($property, 'شقة');
        $this->comparables($property);
        $this->feeShare($request);
    }

    /**
     * @param  array{manager: User, coordinator: User, evaluator: User}  $staff
     */
    private function customTableRequest(Company $company, array $staff, GeoCity $city, GeoNeighborhood $neighborhood): void
    {
        $request = $this->request('DEMO-CUSTOM-1', 91004, RequestState::Waiting2, $company, $staff);
        $property = $this->property($request, 'تجاري', 'معرض', 'عميل الجدول', 'مالك الجدول');
        $this->location($property, $city, $neighborhood, '24.6900', '46.7200');
        $this->borders($property);

        ValuationCustomTableRow::query()->updateOrCreate(
            ['valuation_request_id' => $request->id, 'sort_order' => 0],
            ['table_title' => 'جدول الدخل', 'label' => 'الإيجار السنوي', 'values' => ['120000', '5%']]
        );
        ValuationCustomTableRow::query()->updateOrCreate(
            ['valuation_request_id' => $request->id, 'sort_order' => 1],
            ['table_title' => 'جدول الدخل', 'label' => 'المصاريف', 'values' => ['10000']]
        );
    }

    /**
     * @param  array{manager: User, coordinator: User, evaluator: User}  $staff
     */
    private function attachmentsRequest(Company $company, array $staff, GeoCity $city, GeoNeighborhood $neighborhood): void
    {
        $request = $this->request('DEMO-ATTACH-1', 91005, RequestState::Request, $company, $staff);
        $property = $this->property($request, 'سكني', 'فيلا', 'عميل المرفقات', 'مالك المرفقات');
        $this->location($property, $city, $neighborhood, '24.7001', '46.6801');
        $this->borders($property);

        $imagePath = "valuation-attachments/{$request->id}/front.jpg";
        Storage::disk('local')->put($imagePath, $this->tinyJpeg());
        ValuationAttachment::query()->updateOrCreate(
            ['valuation_request_id' => $request->id, 'type' => 'صورة للعقار'],
            [
                'original_name' => 'front.jpg',
                'path' => $imagePath,
                'disk' => 'local',
                'mime_type' => 'image/jpeg',
                'size' => Storage::disk('local')->size($imagePath),
                'uploaded_by' => $staff['coordinator']->id,
            ]
        );

        $pdfPath = "valuation-attachments/{$request->id}/deed.pdf";
        Storage::disk('local')->put($pdfPath, '%PDF-1.4 demo');
        ValuationAttachment::query()->updateOrCreate(
            ['valuation_request_id' => $request->id, 'type' => 'صورة للصك'],
            [
                'original_name' => 'deed.pdf',
                'path' => $pdfPath,
                'disk' => 'local',
                'mime_type' => 'application/pdf',
                'size' => Storage::disk('local')->size($pdfPath),
                'uploaded_by' => $staff['coordinator']->id,
            ]
        );

        $otherPath = "valuation-attachments/{$request->id}/other.txt";
        Storage::disk('local')->put($otherPath, 'other attachment');
        ValuationAttachment::query()->updateOrCreate(
            ['valuation_request_id' => $request->id, 'type' => 'other_attachments'],
            [
                'original_name' => 'other.txt',
                'path' => $otherPath,
                'disk' => 'local',
                'mime_type' => 'text/plain',
                'size' => Storage::disk('local')->size($otherPath),
                'uploaded_by' => $staff['coordinator']->id,
            ]
        );

        $picturePath = "property-pictures/{$property->id}/north.jpg";
        Storage::disk('local')->put($picturePath, $this->tinyJpeg());
        PropertyPicture::query()->updateOrCreate(
            ['property_id' => $property->id, 'filename' => 'north.jpg'],
            [
                'description' => 'واجهة شمالية',
                'orientation' => 1,
                'sort_order' => 1,
                'relative_path' => $picturePath,
                'file_exists' => true,
            ]
        );
    }

    /**
     * @param  array{manager: User, coordinator: User, evaluator: User}  $staff
     */
    private function approvedRequest(Company $company, array $staff, GeoCity $city, GeoNeighborhood $neighborhood): void
    {
        $official = 'official-reports/DEMO-APPROVED-1.pdf';
        Storage::disk('local')->put($official, '%PDF-1.4 official demo');

        $request = $this->request('DEMO-APPROVED-1', 91006, RequestState::Approve, $company, $staff, [
            'approve' => 1,
            'started_at' => now()->subDays(12),
            'under_evaluation_at' => now()->subDays(8),
            'evaluated_at' => now()->subDays(3),
            'ended_at' => now()->subDay(),
            'uploaded_on_qima' => true,
            'official_report_path' => $official,
            'qima_locked_at' => now()->subHours(6),
        ]);
        $property = $this->property($request, 'سكني', 'فيلا', 'عميل معتمد', 'مالك معتمد');
        $this->location($property, $city, $neighborhood, '24.7130', '46.6750');
        $this->borders($property);
        $this->landDetails($property);
        $this->buildingDetails($property);
        $this->comparables($property);
        $this->costComponents($property);
        $this->totals($property, null);
        $this->feeShare($request);
    }

    /**
     * @param  array{manager: User, coordinator: User, evaluator: User}  $staff
     */
    private function cancelledRequest(Company $company, array $staff, GeoCity $city, GeoNeighborhood $neighborhood): void
    {
        $request = $this->request('DEMO-CANCEL-1', 91007, RequestState::Cancelled, $company, $staff, [
            'started_at' => now()->subDays(6),
        ]);
        $property = $this->property($request, 'سكني', 'أرض', 'عميل ملغي', 'مالك ملغي');
        $this->location($property, $city, $neighborhood, '24.6400', '46.7100');

        if (! $request->trashed()) {
            $request->delete();
        }
    }

    /**
     * @param  array{manager: User, coordinator: User, evaluator: User}  $staff
     */
    private function manualAmountRequest(Company $company, array $staff, GeoCity $city, GeoNeighborhood $neighborhood): void
    {
        $request = $this->request('DEMO-MANUAL-1', 91008, RequestState::Evaluative, $company, $staff, [
            'started_at' => now()->subDays(4),
            'under_evaluation_at' => now()->subDays(2),
        ]);
        $property = $this->property($request, 'سكني', 'فيلا', 'عميل يدوي', 'مالك يدوي');
        $this->location($property, $city, $neighborhood, '24.7500', '46.7000');
        $this->borders($property);
        $this->landDetails($property);
        $this->costComponents($property);
        $this->totals($property, 2_000_000);
        $this->feeShare($request);
    }

    /**
     * @param  array{manager: User, coordinator: User, evaluator: User}  $staff
     * @param  array<string, mixed>  $extra
     */
    private function request(string $number, int $reference, RequestState $state, Company $company, array $staff, array $extra = []): ValuationRequest
    {
        return ValuationRequest::withTrashed()->updateOrCreate(
            ['number' => $number],
            array_merge([
                'reference' => $reference,
                'deposit_number' => 'DEP-'.$reference,
                'company_id' => $company->id,
                'coordinator_user_id' => $staff['coordinator']->id,
                'evaluator_user_id' => $staff['evaluator']->id,
                'sub_user_id' => $staff['manager']->id,
                'fellow_user_id' => $staff['manager']->id,
                'state' => $state->value,
                'approve' => $state === RequestState::Approve ? 1 : 0,
            ], $extra)
        );
    }

    private function property(ValuationRequest $request, string $kind, string $type, string $customer, string $owner): Property
    {
        return Property::query()->updateOrCreate(
            ['valuation_request_id' => $request->id],
            [
                'customer_name' => $customer,
                'owner_name' => $owner,
                'customer_name_en' => $customer,
                'owner_name_en' => $owner,
                'property_kind' => $kind,
                'property_type' => $type,
                'instrument_no' => '31'.$request->reference.'01',
                'instrument_date' => '1445/01/01',
                'instrument_gregorian_date' => '2023-07-19',
                'valuation_type' => 1,
                'base_value' => 1,
                'value_assumption' => 4,
                'valuation_usage' => 1,
                'market_valuation_way_main' => 1,
                'cost_valuation_way_main' => 1,
                'informations_source' => 'منصة السوق',
                'evaluation_date' => now()->toDateString(),
                'evaluation_date_hijri' => '1448/04/09',
                'deposit_number' => $request->deposit_number,
                'notes' => 'بيانات تجريبية',
            ]
        );
    }

    private function location(Property $property, GeoCity $city, GeoNeighborhood $neighborhood, string $x, string $y): void
    {
        PropertyLocation::query()->updateOrCreate(
            ['property_id' => $property->id],
            [
                'city_id' => $city->id,
                'neighborhood_id' => $neighborhood->id,
                'sketch_name' => 'مخطط الاختبار',
                'street' => 'شارع الاختبار',
                'sketch_no' => '1234',
                'part_no' => '12',
                'piece_number' => '56',
                'x_axis' => $x,
                'y_axis' => $y,
            ]
        );
    }

    private function borders(Property $property): void
    {
        PropertyBorder::query()->updateOrCreate(
            ['property_id' => $property->id],
            [
                'north' => 'شارع عرض 20م',
                'north_length' => 25,
                'south' => 'جار',
                'south_length' => 25,
                'east' => 'جار',
                'east_length' => 20,
                'west' => 'ممر',
                'west_length' => 20,
            ]
        );
    }

    private function landDetails(Property $property, float $buildingArea = 0): void
    {
        PropertyLand::query()->updateOrCreate(
            ['property_id' => $property->id],
            [
                'land_nature' => 'مستوية',
                'facade' => 'شمالية',
                'site' => 'داخلي',
                'street' => 'سكني',
                'neighbor' => 'سكني',
                'include' => 'غرف',
                'heater_no' => '2',
                'lift_no' => '1',
                'bathroom_a_no' => '3',
                'bathroom_e_no' => '2',
                'ownership_type' => 1,
                'building_type' => 1,
                'finishing_status' => 1,
                'street_width' => 20,
                'total_building_area' => $buildingArea,
                'approved_floors' => 2,
                'property_use' => 'سكني',
                'surrounding_facilities' => 'مسجد,مدرسة',
            ]
        );
        PropertyFacade::query()->updateOrCreate(
            ['property_id' => $property->id],
            ['facade_type_n' => 'حجر', 'facade_type_s' => 'دهان', 'facade_type_e' => 'دهان', 'facade_type_w' => 'دهان']
        );
        PropertyService::query()->updateOrCreate(
            ['property_id' => $property->id],
            ['electricity' => 'نعم', 'water' => 'نعم', 'sanitation' => 'نعم', 'telephone' => 'نعم', 'internet' => 'نعم']
        );
        PropertyQuestion::query()->updateOrCreate(
            ['property_id' => $property->id],
            [
                'populated' => 'نعم',
                'ready' => 'نعم',
                'identical' => 'نعم',
                'contrary' => 'لا',
                'concrete_roof' => 'نعم',
                'ground_extension' => 'لا',
                'need_cost' => 'لا',
                'inside_inspection' => 'نعم',
                'best_use' => 1,
            ]
        );
    }

    private function buildingDetails(Property $property, string $kind = 'فيلا'): void
    {
        PropertyBuilding::query()->updateOrCreate(
            ['property_id' => $property->id],
            [
                'kind' => $kind,
                'floor_no' => 2,
                'build_age' => '10',
                'build_state' => 'جيد',
                'architectural' => 'حديث',
                'quality_insulation' => 'جيد',
                'structure_construction' => 'خرساني',
                'default_build_age' => 50,
                'remaining_build_age' => 40,
            ]
        );
        PropertyFlooring::query()->updateOrCreate(
            ['property_id' => $property->id],
            ['flooring_yards' => 'بلاط', 'flooring_reception' => 'رخام', 'flooring_entrance' => 'رخام', 'flooring_rooms' => 'سيراميك']
        );
        PropertyAc::query()->updateOrCreate(
            ['property_id' => $property->id],
            ['types' => 'سبليت', 'ac_count' => 6]
        );
    }

    private function comparables(Property $property): void
    {
        $rows = [
            1 => ['area' => 500, 'price' => 3000, 'market' => -2],
            2 => ['area' => 600, 'price' => 2800, 'market' => 0],
            3 => ['area' => 550, 'price' => 3100, 'market' => 2],
        ];

        foreach ($rows as $sequence => $row) {
            PropertyComparable::query()->updateOrCreate(
                ['property_id' => $property->id, 'sequence' => $sequence],
                [
                    'real_estate_type' => 'أرض',
                    'street_width' => 20,
                    'front_number' => 1,
                    'front_type' => 'شمالية',
                    'area' => $row['area'],
                    'price' => $row['price'],
                    'percentage' => 100,
                    'coordinates_url' => 'https://www.google.com/maps/@24.71'.$sequence.',46.67'.$sequence.',17z',
                    'information_source' => 'منصة السوق',
                ]
            );
            PropertyAdjustment::query()->updateOrCreate(
                ['property_id' => $property->id, 'type' => $sequence],
                [
                    'transaction_date' => now()->subMonths($sequence)->toDateString(),
                    'market_conditions_percentage' => $row['market'],
                    'area_percentage' => $row['market'],
                    'location_status_value' => 1,
                    'location_status_percentage' => 0,
                    'proportional_adjustment' => 30,
                    'meter_price' => 3050,
                    'meter_price_approximately' => 3000,
                ]
            );
        }
    }

    private function costComponents(Property $property): void
    {
        foreach (['land_area' => [500, 3000], 'ground' => [300, 1500], 'upper' => [150, 1200]] as $key => [$area, $price]) {
            PropertyComponent::query()->updateOrCreate(
                ['property_id' => $property->id, 'component_key' => $key],
                ['area_value' => $area, 'price_value' => $price]
            );
        }
    }

    private function totals(Property $property, ?int $manual): void
    {
        PropertyTotal::query()->updateOrCreate(
            ['property_id' => $property->id],
            [
                'profit_percentage' => 10,
                'profit_amount' => 63_000,
                'depreciation_type' => 1,
                'depreciation_amount' => 138_600,
                'forced_sale_percentage' => 15,
                'forced_sale_amount' => 300_000,
                'total_amount' => 2_054_400,
                'total_amount_manual' => $manual,
                'movables' => 0,
            ]
        );
    }

    private function feeShare(ValuationRequest $request): void
    {
        RequestFeeShare::query()->updateOrCreate(
            ['valuation_request_id' => $request->id],
            ['coordinator_share' => 20, 'evaluator_share' => 50, 'manager_share' => 30]
        );
    }

    private function linkCommercial(ValuationRequest $request, Partner $partner, Contractor $contractor, Offer $offer, OfferEstate $estate): void
    {
        $offer->update(['valuation_request_id' => $request->id]);

        Contract::query()->updateOrCreate(
            ['valuation_request_id' => $request->id, 'contractor_id' => $contractor->id],
            ['state' => Contract::STATE_UNPAID]
        );

        PartnerContract::query()->updateOrCreate(
            ['valuation_request_id' => $request->id],
            [
                'partner_id' => $partner->id,
                'offer_estate_id' => $estate->id,
                'state' => 1,
            ]
        );
    }

    private function tinyJpeg(): string
    {
        return base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/2wAAADwABQABBAQBBAQAAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAj/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIQAxAAAAGP/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPwB//9k=', true) ?: 'demo';
    }
}
