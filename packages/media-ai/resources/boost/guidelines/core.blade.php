Media AI adds an AI "Doctor image" header action to image records in the Capell
Admin media library. The action resolves the `ImageDoctor` contract, sends an
`ImageDoctorRequest` (operation + instructions), and surfaces the
`ImageDoctorResult` message as a Filament notification. It does not itself write
any metadata onto media records. A `NullImageDoctor` is bound by default, so the
action stays hidden until a real provider is bound. Require record-level media
authorization and keep provider behavior behind the contract.
